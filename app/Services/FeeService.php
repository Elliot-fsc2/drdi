<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeeService
{
    /**
     * Initialize the group's ledger record using the semester's base rate.
     *
     * This method is safe to call even if the group has no existing `fee` record:
     * it will create the fee record with sensible defaults.
     *
     * @param Group $group The group to initialize the ledger for.
     * @return void
     */
    public function initializeGroupLedger(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = $group->section->semester;

            // Obtain the fixed-per-group master rate for the semester.
            $baseRate = $semester->rates()
                ->where('type', 'fixed_per_group')
                ->first();

            $baseAmount = $baseRate->amount ?? 0;

            // Preserve any existing honorarium total if present, otherwise default to 0.
            $existingHonorarium = optional($group->fee)->honorarium_total ?? 0;

            $fee = $group->fee()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'base_fee' => $baseAmount,
                    'honorarium_total' => $existingHonorarium,
                    'total_merger_amount' => $baseAmount + $existingHonorarium,
                ]
            );

            // Activity log: record that we initialized or ensured the group's fee ledger.
            activity()->useLog('group_fees')
                ->performedOn($fee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'group_id' => $group->id,
                    'semester_id' => optional($semester)->id,
                    'base_fee' => $baseAmount,
                    'honorarium_total' => $existingHonorarium,
                    'total_merger_amount' => $baseAmount + $existingHonorarium,
                ])
                ->log('Initialized group fee ledger');
        });
    }

    /**
     * Recalculate and persist the honorarium totals for a group.
     *
     * The honorarium is derived from the semester's `per_personnel` rate
     * multiplied by the number of personnel attached to the group.
     * This method will create the group's fee record if it does not exist.
     *
     * @param Group $group
     * @return void
     */
    public function syncHonorarium(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = $group->section->semester;

            // Per-personnel rate for this semester (may be null)
            $personnelRate = $semester->rates()
                ->where('type', 'per_personnel')
                ->first();

            $personnelCount = $group->personnel()->count();
            $totalHonorarium = ($personnelRate->amount ?? 0) * $personnelCount;

            // Ensure there's a base fee to compute the merged total (default 0)
            $currentBaseFee = optional($group->fee)->base_fee ?? 0;

            $previousHonorarium = optional($group->fee)->honorarium_total ?? 0;

            // Use updateOrCreate so the fee record is present after this call.
            $fee = $group->fee()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'base_fee' => $currentBaseFee,
                    'honorarium_total' => $totalHonorarium,
                    'total_merger_amount' => $currentBaseFee + $totalHonorarium,
                ]
            );

            // Activity log: record honorarium sync details.
            activity()->useLog('group_fees')
                ->performedOn($fee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'group_id' => $group->id,
                    'semester_id' => optional($semester)->id,
                    'personnel_count' => $personnelCount,
                    'previous_honorarium' => $previousHonorarium,
                    'new_honorarium' => $totalHonorarium,
                    'base_fee' => $currentBaseFee,
                ])
                ->log('Synchronized honorarium totals for group');
        });
    }

    /**
     * Create or update the master rates for a semester.
     *
     * Accepts either a `Semester` model instance or a semester id in
     * `$data['semester_id']`.
     *
     * Expected `$data` keys: `semester_id`, `fixed_per_group`, `per_personnel`.
     *
     * @param array $data
     * @return void
     * @throws InvalidArgumentException When `semester_id` is not valid.
     */
    public function createRates(array $data): void
    {
        DB::transaction(function () use ($data) {
            $semester = $data['semester_id'] ?? null;

            // Accept either an ID or a Semester instance
            if (is_int($semester) || is_string($semester)) {
                $semester = Semester::findOrFail($semester);
            }

            if (! $semester instanceof Semester) {
                throw new InvalidArgumentException('The semester_id must be a Semester instance or a valid semester id.');
            }

            // Create or update the fixed per group rate
            $fixedRate = $semester->rates()->updateOrCreate(
                ['type' => 'fixed_per_group'],
                ['amount' => $data['fixed_per_group'] ?? 0]
            );

            // Create or update the per personnel rate
            $personnelRate = $semester->rates()->updateOrCreate(
                ['type' => 'per_personnel'],
                ['amount' => $data['per_personnel'] ?? 0]
            );

            // Activity log: record rate creation/update for the semester.
            activity()->useLog('group_fees')
                ->performedOn($semester)
                ->causedBy(auth()->user())
                ->withProperties([
                    'semester_id' => $semester->id,
                    'fixed_per_group' => $fixedRate->amount,
                    'per_personnel' => $personnelRate->amount,
                ])
                ->log('Created or updated semester master rates');
        });
    }

    /**
     * Recalculate fees for every group in the provided semester.
     *
     * This method streams sections in chunks to keep memory usage low and
     * eager-loads `groups.personnel` so we avoid N+1 queries while computing
     * honorarium totals.
     *
     * @param Semester $semester
     * @return void
     */
    public function updateAllGroupsInSemester(Semester $semester): void
    {
        // Aggregate master rates for the semester (may be multiple entries,
        // sum them defensively so callers can seed multiple rate records).
        $baseRateTotal = $semester->rates()
            ->where('type', 'fixed_per_group')
            ->sum('amount');

        $perPersonnelRateTotal = $semester->rates()
            ->where('type', 'per_personnel')
            ->sum('amount');

        // Stream active sections with groups and personnel to avoid loading
        // the entire semester into memory at once.
        $semester->sections()
            ->active()
            ->with('groups.personnel')
            ->chunkById(100, function ($sections) use ($baseRateTotal, $perPersonnelRateTotal) {
                foreach ($sections as $section) {
                    foreach ($section->groups as $group) {
                        $personnelCount = $group->personnel->count();
                        $honorariumTotal = $perPersonnelRateTotal * $personnelCount;

                        $fee = $group->fee()->updateOrCreate(
                            ['group_id' => $group->id],
                            [
                                'base_fee' => $baseRateTotal,
                                'honorarium_total' => $honorariumTotal,
                                'total_merger_amount' => $baseRateTotal + $honorariumTotal,
                            ]
                        );

                        // Activity log: record that we updated this group's fee during bulk update.
                        activity()->useLog('group_fees')
                            ->performedOn($fee)
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'group_id' => $group->id,
                                'section_id' => optional($section)->id,
                                'semester_id' => optional($section->semester)->id ?? null,
                                'personnel_count' => $personnelCount,
                                'base_fee' => $baseRateTotal,
                                'honorarium_total' => $honorariumTotal,
                            ])
                            ->log('Updated group fee during semester bulk update');
                    }
                }
            });
    }
}
