<?php

namespace App\Services;

use App\Models\Group;

class FeeService
{
    /**
     * Initialize the group ledger with the base fee from the semester.
     */
    public function initializeGroupLedger(Group $group): void
    {
        $semester = $group->section->semester;

        // Find the 'fixed_per_group' rate bound to this semester
        $baseRate = $semester->rates()
            ->where('type', 'fixed_per_group')
            ->first();

        $group->fee()->updateOrCreate(
            ['group_id' => $group->id],
            ['base_fee' => $baseRate->amount ?? 0]
        );
    }

    /**
     * Recalculate the honorarium portion based on personnel count.
     */
    public function syncHonorarium(Group $group): void
    {
        $semester = $group->section->semester;

        // Find the 'per_personnel' rate for this semester
        $hRate = $semester->rates()
            ->where('type', 'per_personnel')
            ->first();

        $personnelCount = $group->personnel()->count();
        $totalHonorarium = ($hRate->amount ?? 0) * $personnelCount;

        $group->fee()->update([
            'honorarium_total' => $totalHonorarium,
        ]);
    }
}
