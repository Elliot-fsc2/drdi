<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class FeeService
{
    /**
     * Initialize the group ledger with the base fee from the semester.
     */
    public function initializeGroupLedger(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = $group->section->semester;

            // Find the 'fixed_per_group' rate bound to this semester
            $baseRate = $semester->rates()
                ->where('type', 'fixed_per_group')
                ->first();

            $amount = $baseRate->amount ?? 0;

            $group->fee()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'base_fee' => $amount,
                    // Total is just the base fee since honorarium is usually 0 at init
                    'total_merger_amount' => $amount + ($group->fee->honorarium_total ?? 0),
                ]
            );
        });
    }

    /**
     * Recalculate the honorarium portion based on personnel count.
     */
    public function syncHonorarium(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = $group->section->semester;

            // Find the 'per_personnel' rate for this semester
            $hRate = $semester->rates()
                ->where('type', 'per_personnel')
                ->first();

            $personnelCount = $group->personnel()->count();
            $totalHonorarium = ($hRate->amount ?? 0) * $personnelCount;
            // Fetch existing base fee to compute the new merger total
            $currentBaseFee = $group->fee->base_fee ?? 0;

            $group->fee()->update([
                'honorarium_total' => $totalHonorarium,
                'total_merger_amount' => $currentBaseFee + $totalHonorarium,
            ]);
        });
    }

    public function createRates(array $data): void
    {
        DB::transaction(function () use ($data) {
            $semester = $data['semester_id'];

            // Create or update the fixed per group rate
            $semester->rates()->updateOrCreate(
                ['type' => 'fixed_per_group'],
                ['amount' => $data['fixed_per_group']]
            );

            // Create or update the per personnel rate
            $semester->rates()->updateOrCreate(
                ['type' => 'per_personnel'],
                ['amount' => $data['per_personnel']]
            );
        });
    }

    public function updateAllGroupsInSemester(Semester $semester): void
    {
        // 1. Calculate the aggregate Master Rates for this semester
        $totalBaseAmount = $semester->rates()
            ->where('type', 'fixed_per_group')
            ->sum('amount');

        $totalPersonnelRate = $semester->rates()
            ->where('type', 'per_personnel')
            ->sum('amount');

        // 2. Load sections, their groups, and the groups' personnel
        // We use chunkById to handle large amounts of data efficiently
        $semester->sections()->active()->with('groups.personnel')->chunkById(100, function ($sections) use ($totalBaseAmount, $totalPersonnelRate) {
            foreach ($sections as $section) {
                foreach ($section->groups as $group) {
                    // Calculate personnel count
                    $personnelCount = $group->personnel->count();
                    $totalHonorarium = $totalPersonnelRate * $personnelCount;

                    // Update or create the group fee
                    $group->fee()->updateOrCreate(
                        ['group_id' => $group->id],
                        [
                            'base_fee' => $totalBaseAmount,
                            'honorarium_total' => $totalHonorarium,
                            'total_merger_amount' => $totalBaseAmount + $totalHonorarium,
                        ]
                    );
                }
            }
        });
    }
}
