<?php

namespace App\Services;

use App\Enums\PersonnelRole;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupService
{
    public function create(array $data): Group
    {
        return DB::transaction(function () use ($data) {
            $group = Group::create([
                'name' => $data['name'],
                'section_id' => $data['section_id'],
                'leader_id' => $data['leader_id'] ?? null,
            ]);

            // Attach members if provided
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $group->members()->attach($data['member_ids']);
            }

            $this->assignRandomPersonnel($group);

            activity('Group Creation')
                ->performedOn($group)
                ->causedBy(Auth::user())
                ->withProperties([
                    'group_id' => $group->id,
                    'section_id' => $group->section_id,
                    'leader_id' => $group->leader_id,
                ])
                ->log(sprintf('Created group %s in section %s.', $group->name, $group->section->name));

            return $group->load(['section', 'leader', 'members']);
        });
    }

    public function update(Group $group, array $data): Group
    {
        return DB::transaction(function () use ($group, $data) {
            $group->update([
                'name' => $data['name'] ?? $group->name,
                'section_id' => $data['section_id'] ?? $group->section_id,
                'leader_id' => $data['leader_id'] ?? $group->leader_id,
            ]);

            // Sync members if provided
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $group->members()->sync($data['member_ids']);
            }

            return $group->fresh(['section', 'leader', 'members']);
        });
    }

    public function delete(Group $group): bool
    {
        return DB::transaction(function () use ($group) {
            $group->members()->detach();

            $group->consultations()->delete();
            $group->personnel()->delete();
            $group->proposals()?->delete();
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
        $group->members()->syncWithoutDetaching($studentIds);

        return $group->fresh('members');
    }

    public function removeMembers(Group $group, array $studentIds): Group
    {
        $group->members()->detach($studentIds);

        return $group->fresh('members');
    }

    public function removeStudentFromSectionGroups(int $studentId, int $sectionId): void
    {
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
    }

    private function assignRandomPersonnel(Group $group): void
    {
        $assignedInstructorIds = [];

        foreach ($this->personnelRolesToAssign() as $role) {
            $instructor = $this->findRandomEligibleInstructor($group, $assignedInstructorIds);

            if (! $instructor) {
                continue;
            }

            $personnel = $this->createPersonnelAssignment($group, $instructor, $role);

            $assignedInstructorIds[] = $instructor->id;

            $this->logPersonnelAssignment($group, $personnel, $instructor, $role);
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

    private function logPersonnelAssignment(
        Group $group,
        Personnel $personnel,
        Instructor $instructor,
        PersonnelRole $role,
    ): void {
        activity('Personnel Assignment')
            ->performedOn($group)
            ->causedBy(Auth::user())
            ->withProperties([
                'personnel_id' => $personnel->id,
                'personnel_name' => $instructor->full_name,
                'group_id' => $group->id,
                'section' => $group->section->name,
                'thesis_advisor' => $group->section->instructor->full_name,
                'role' => $role->value,
            ])
            ->log(sprintf(
                'Automatically assigned %s to group %s using instructor %s.',
                $role->getLabel(),
                $group->name,
                $instructor->full_name,
            ));
    }
}
