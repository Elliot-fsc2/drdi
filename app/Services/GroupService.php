<?php

namespace App\Services;

use App\Enums\PersonnelRole;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class GroupService
{
    public function create(array $data): Group
    {
        Gate::authorize('create_groups');

        return DB::transaction(function () use ($data) {
            $group = Group::create([
                'name' => $data['name'],
                'section_id' => $data['section_id'],
                'leader_id' => $data['leader_id'] ?? null,
            ]);

            // Attach members if provided
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $this->attachGroupMembers($group, $data['member_ids']);
            }

            $this->assignRandomPersonnel($group);

            return $group->load(['section', 'leader', 'members']);
        });
    }

    public function update(Group $group, array $data)
    {
        Gate::authorize('update_groups');

        DB::transaction(function () use ($group, $data) {
            $group->update([
                'name' => $data['name'] ?? $group->name,
                'section_id' => $data['section_id'] ?? $group->section_id,
                'leader_id' => $data['leader_id'] ?? $group->leader_id,
            ]);

            // Sync members if provided
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $this->syncGroupMembers($group, $data['member_ids']);
            }

            return $group->fresh(['section', 'leader', 'members']);
        });
    }

    public function delete(Group $group)
    {
        Gate::authorize('delete_groups');

        DB::transaction(function () use ($group) {
            $group->members()->detach();

            $group->updateQuietly(['final_title_id' => null]);

            $group->consultations()->delete();
            $group->personnel()->delete();
            $group->proposals()->delete();
            $group->fee()?->delete();

            return $group->delete();
        });

    }

    public function find(int $id): ?Group
    {
        return Group::with(['section', 'leader', 'members', 'proposal', 'consultations', 'personnel', 'fee'])
            ->find($id);
    }

    public function all()
    {
        return Group::with(['section', 'leader', 'members'])
            ->latest()
            ->get();
    }

    public function addMembers(Group $group, array $studentIds): Group
    {
        Gate::authorize('update_groups');

        $group->members()->syncWithoutDetaching($studentIds);

        return $group->fresh('members');
    }

    public function removeMembers(Group $group, array $studentIds): Group
    {
        Gate::authorize('update_groups');

        $group->members()->detach($studentIds);

        return $group->fresh('members');
    }

    public function removeStudentFromSectionGroups(int $studentId, int $sectionId): void
    {
        Gate::authorize('update_groups');

        DB::transaction(function () use ($studentId, $sectionId) {
            // Find all groups in this section that the student belongs to
            $groups = Group::where('section_id', $sectionId)
                ->where(function ($query) use ($studentId) {
                    $query->where('leader_id', $studentId)
                        ->orWhereHas('members', function ($q) use ($studentId) {
                            $q->where('students.id', $studentId);
                        });
                })
                ->get();

            foreach ($groups as $group) {
                // If student is the leader, unset leader_id
                if ($group->leader_id === $studentId) {
                    $group->update(['leader_id' => null]);
                }

                // Remove student from group members
                $group->members()->detach($studentId);
            }
        });

        /*
        activity('Student Removal')
            ->causedBy(Auth::user())
            ->withProperties([
                'student_id' => $studentId,
                'section_id' => $sectionId,
            ])
            ->log(sprintf('Removed student ID %d from all groups in section ID %d. (by %s)', $studentId, $sectionId, Auth::user()?->name ?? 'system'));
        */
    }

    private function assignRandomPersonnel(Group $group): void
    {
        $assignedInstructorIds = [];
        $assignedPersonnel = [];

        foreach ($this->personnelRolesToAssign() as $role) {
            $instructor = $this->findRandomEligibleInstructor($group, $assignedInstructorIds);

            if (! $instructor) {
                continue;
            }

            $personnel = $this->createPersonnelAssignment($group, $instructor, $role);

            $assignedInstructorIds[] = $instructor->id;
            $assignedPersonnel[] = [
                'personnel_id' => $personnel->id,
                'personnel_name' => $instructor->full_name,
                'role' => $role->value,
            ];
        }

        if ($assignedPersonnel !== []) {
            $this->logPersonnelAssignments($group, $assignedPersonnel);
        }
    }

    /**
     * @return array<int, PersonnelRole>
     */
    private function personnelRolesToAssign(): array
    {
        return [
            PersonnelRole::STATISTICIAN,
            PersonnelRole::LANGUAGE_CRITIC,
        ];
    }

    /**
     * @param  array<int, int>  $excludedInstructorIds
     */
    private function findRandomEligibleInstructor(Group $group, array $excludedInstructorIds = []): ?Instructor
    {
        return Instructor::query()
            ->whereDoesntHave('personnel', function ($query) use ($group) {
                $query->where('group_id', $group->id);
            })
            ->whereNotIn('id', $excludedInstructorIds)
            ->inRandomOrder()
            ->first();
    }

    private function createPersonnelAssignment(Group $group, Instructor $instructor, PersonnelRole $role): Personnel
    {
        return Personnel::create([
            'instructor_id' => $instructor->id,
            'group_id' => $group->id,
            'role' => $role,
        ]);
    }

    private function attachGroupMembers(Group $group, array $memberIds): void
    {
        $group->members()->attach($memberIds);

        activity('Group members attachment')
            ->performedOn($group)
            ->causedBy(Auth::user())
            ->withProperties([
                'members' => $memberIds,
            ])
            ->log('Attached members to group: :members');
    }

    private function syncGroupMembers(Group $group, array $memberIds): void
    {
        $group->members()->sync($memberIds);

        activity('Group members synchronization')
            ->performedOn($group)
            ->causedBy(Auth::user())
            ->withProperties([
                'members' => $memberIds,
            ])
            ->log('Synced members for group: :members');
    }

    private function logPersonnelAssignments(Group $group, array $assignedPersonnel): void
    {
        activity('Personnel Assignment')
            ->performedOn($group)
            ->causedBy(Auth::user())
            ->withProperties([
                'group_id' => $group->id,
                'section' => $group->section->name,
                'thesis_advisor' => $group->section->instructor->full_name,
                'assigned_personnel' => $assignedPersonnel,
            ])
            ->log(sprintf(
                'Automatically assigned personnel to group %s. (by %s)',
                $group->name,
                Auth::user()?->name ?? 'system',
            ));
    }
}
