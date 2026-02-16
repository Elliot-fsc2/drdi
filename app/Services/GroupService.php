<?php

namespace App\Services;

use App\Models\Group;
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
            // Detach many-to-many relationships
            $group->members()->detach();

            // Delete related records
            $group->consultations()->delete();
            $group->personnel()->delete();
            $group->proposal()?->delete();
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
}
