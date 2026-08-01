<?php

namespace App\Services;

use App\Enums\PanelistRole;
use App\Enums\ThesisRatesType;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Semester;
use App\Notifications\FeeLedgerInitialized;
use App\Notifications\SemesterRateUpdated;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeeService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function initializeGroupLedger(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = Semester::whereHas('sections', fn ($q) => $q->where('id', $group->section_id))->firstOrFail();

            $baseAmount = $semester->rates()
                ->where('type', ThesisRatesType::FIXED_PER_GROUP->value)
                ->sum('amount');

            $existingHonorarium = optional($group->fee)->honorarium_total ?? 0;
            $existingPanelFee = optional($group->fee)->panel_fee_total ?? 0;

            $fee = $group->fee()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'base_fee' => $baseAmount,
                    'honorarium_total' => $existingHonorarium,
                    'panel_fee_total' => $existingPanelFee,
                    'total_merger_amount' => $baseAmount + $existingHonorarium + $existingPanelFee,
                ]
            );

            activity('Group Fee Initialization')
                ->performedOn($fee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'group_id' => $group->id,
                    'semester_id' => optional($semester)->id,
                    'base_fee' => $baseAmount,
                    'honorarium_total' => $existingHonorarium,
                    'panel_fee_total' => $existingPanelFee,
                    'total_merger_amount' => $baseAmount + $existingHonorarium + $existingPanelFee,
                ])
                ->log('Initialized group fee ledger');

            $this->notificationService->sendToGroupMembers($group, new FeeLedgerInitialized($group));
        });
    }

    public function syncHonorarium(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = Semester::whereHas('sections', fn ($q) => $q->where('id', $group->section_id))->firstOrFail();

            $totalHonorarium = 0;
            $personnelDetails = [];

            foreach ($group->personnel as $personnel) {
                $roleRates = $semester->rates()
                    ->where('type', ThesisRatesType::PER_PERSONNEL->value)
                    ->where('personnel_role', $personnel->role->value)
                    ->sum('amount');

                $generalRates = $semester->rates()
                    ->where('type', ThesisRatesType::PER_PERSONNEL->value)
                    ->whereNull('personnel_role')
                    ->sum('amount');

                $personTotal = $roleRates + $generalRates;
                $totalHonorarium += $personTotal;

                $personnelDetails[] = [
                    'personnel_id' => $personnel->id,
                    'role' => $personnel->role->value,
                    'role_specific' => $roleRates,
                    'general' => $generalRates,
                    'total' => $personTotal,
                ];
            }

            $currentBaseFee = optional($group->fee)->base_fee ?? 0;
            $previousHonorarium = optional($group->fee)->honorarium_total ?? 0;
            $currentPanelFee = optional($group->fee)->panel_fee_total ?? 0;

            $fee = $group->fee()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'base_fee' => $currentBaseFee,
                    'honorarium_total' => $totalHonorarium,
                    'panel_fee_total' => $currentPanelFee,
                    'total_merger_amount' => $currentBaseFee + $totalHonorarium + $currentPanelFee,
                ]
            );

            activity()->useLog('group_fees')
                ->performedOn($fee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'group_id' => $group->id,
                    'semester_id' => optional($semester)->id,
                    'personnel_breakdown' => $personnelDetails,
                    'previous_honorarium' => $previousHonorarium,
                    'new_honorarium' => $totalHonorarium,
                    'base_fee' => $currentBaseFee,
                ])
                ->log('Synchronized honorarium totals for group');

        });
    }

    public function syncPanelFees(Schedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            $semester = Semester::whereHas('sections', fn ($q) => $q->where('id', $schedule->section_id))->firstOrFail();
            $panelists = $schedule->panelists ?? [];

            $totalPanelFee = 0;
            $panelistDetails = [];

            foreach ($panelists as $instructorId => $roleValue) {
                $role = PanelistRole::from($roleValue);

                $roleRates = $semester->rates()
                    ->where('type', ThesisRatesType::PER_PANEL->value)
                    ->where('panelist_role', $role->value)
                    ->sum('amount');

                $generalRates = $semester->rates()
                    ->where('type', ThesisRatesType::PER_PANEL->value)
                    ->whereNull('panelist_role')
                    ->sum('amount');

                $personTotal = $roleRates + $generalRates;
                $totalPanelFee += $personTotal;

                $panelistDetails[] = [
                    'instructor_id' => (int) $instructorId,
                    'role' => $role->value,
                    'role_specific' => $roleRates,
                    'general' => $generalRates,
                    'total' => $personTotal,
                ];
            }

            $schedule->update(['panel_fee' => $totalPanelFee]);

            $group = $schedule->group;
            if ($group) {
                $totalFromSchedules = $group->schedules()->sum('panel_fee');
                $fee = $group->fee;
                if ($fee) {
                    $fee->update([
                        'panel_fee_total' => $totalFromSchedules,
                        'total_merger_amount' => $fee->base_fee + $fee->honorarium_total + $totalFromSchedules,
                    ]);
                }
            }

            activity()->useLog('group_fees')
                ->performedOn($schedule)
                ->causedBy(auth()->user())
                ->withProperties([
                    'schedule_id' => $schedule->id,
                    'group_id' => $schedule->group_id,
                    'semester_id' => optional($semester)->id,
                    'panelist_breakdown' => $panelistDetails,
                    'total_panel_fee' => $totalPanelFee,
                ])
                ->log('Synchronized panel fees for schedule');
        });
    }

    public function recalcPanelFeesForGroup(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $semester = Semester::whereHas('sections', fn ($q) => $q->where('id', $group->section_id))->firstOrFail();

            foreach ($group->schedules as $schedule) {
                $panelists = $schedule->panelists ?? [];
                $total = 0;

                foreach ($panelists as $instructorId => $roleValue) {
                    $role = PanelistRole::from($roleValue);

                    $roleRates = $semester->rates()
                        ->where('type', ThesisRatesType::PER_PANEL->value)
                        ->where('panelist_role', $role->value)
                        ->sum('amount');

                    $generalRates = $semester->rates()
                        ->where('type', ThesisRatesType::PER_PANEL->value)
                        ->whereNull('panelist_role')
                        ->sum('amount');

                    $total += $roleRates + $generalRates;
                }

                $schedule->update(['panel_fee' => $total]);
            }

            $totalFromSchedules = $group->schedules()->sum('panel_fee');

            $group->fee()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'panel_fee_total' => $totalFromSchedules,
                    'total_merger_amount' => ($group->fee->base_fee ?? 0) + ($group->fee->honorarium_total ?? 0) + $totalFromSchedules,
                ]
            );
        });
    }

    public function createRates(array $data): void
    {
        DB::transaction(function () use ($data) {
            $semester = $data['semester_id'] ?? null;

            if (is_int($semester) || is_string($semester)) {
                $semester = Semester::findOrFail($semester);
            }

            if (! $semester instanceof Semester) {
                throw new InvalidArgumentException('The semester_id must be a Semester instance or a valid semester id.');
            }

            if (array_key_exists('fixed_per_group', $data)) {
                $semester->rates()->updateOrCreate(
                    ['type' => ThesisRatesType::FIXED_PER_GROUP->value, 'name' => 'Base Fee'],
                    ['amount' => $data['fixed_per_group'] ?? 0]
                );
            }

            if (array_key_exists('per_personnel', $data)) {
                $semester->rates()->updateOrCreate(
                    ['type' => ThesisRatesType::PER_PERSONNEL->value, 'name' => 'Personnel Honorarium'],
                    ['amount' => $data['per_personnel'] ?? 0]
                );
            }

            activity()->useLog('group_fees')
                ->performedOn($semester)
                ->causedBy(auth()->user())
                ->withProperties([
                    'semester_id' => $semester->id,
                ])
                ->log('Created or updated semester master rates');
        });
    }

    public function notifySemesterRateChange(Semester $semester): void
    {
        $groups = Group::query()
            ->whereHas('section.semester', fn ($q) => $q->where('id', $semester->id))
            ->with('section.semester')
            ->get();

        foreach ($groups as $group) {
            $this->notificationService->sendToGroupMembers($group, new SemesterRateUpdated($semester));
        }
    }

    public function updateAllGroupsInSemester(Semester $semester): void
    {
        $baseRateTotal = $semester->rates()
            ->where('type', ThesisRatesType::FIXED_PER_GROUP->value)
            ->sum('amount');

        $updatedGroupCount = 0;

        $semester->sections()
            ->active()
            ->with('groups.section', 'groups.personnel', 'groups.schedules', 'groups.fee')
            ->chunkById(100, function ($sections) use ($semester, $baseRateTotal, &$updatedGroupCount) {
                foreach ($sections as $section) {
                    foreach ($section->groups as $group) {
                        $honorariumTotal = 0;

                        foreach ($group->personnel as $personnel) {
                            $roleRates = $this->getRoleRates(
                                $semester,
                                ThesisRatesType::PER_PERSONNEL,
                                'personnel_role',
                                $personnel->role->value,
                            );

                            $generalRates = $this->getRoleRates(
                                $semester,
                                ThesisRatesType::PER_PERSONNEL,
                                'personnel_role',
                                null,
                            );

                            $honorariumTotal += $roleRates + $generalRates;
                        }

                        $panelFeeTotal = 0;

                        foreach ($group->schedules as $schedule) {
                            $panelists = $schedule->panelists ?? [];
                            $scheduleTotal = 0;

                            foreach ($panelists as $instructorId => $roleValue) {
                                $role = PanelistRole::from($roleValue);

                                $roleRates = $this->getRoleRates(
                                    $semester,
                                    ThesisRatesType::PER_PANEL,
                                    'panelist_role',
                                    $role->value,
                                );

                                $generalRates = $this->getRoleRates(
                                    $semester,
                                    ThesisRatesType::PER_PANEL,
                                    'panelist_role',
                                    null,
                                );

                                $scheduleTotal += $roleRates + $generalRates;
                            }

                            $schedule->update(['panel_fee' => $scheduleTotal]);
                            $panelFeeTotal += $scheduleTotal;
                        }

                        $group->fee()->updateOrCreate(
                            ['group_id' => $group->id],
                            [
                                'base_fee' => $baseRateTotal,
                                'honorarium_total' => $honorariumTotal,
                                'panel_fee_total' => $panelFeeTotal,
                                'total_merger_amount' => $baseRateTotal + $honorariumTotal + $panelFeeTotal,
                            ]
                        );

                        $updatedGroupCount++;
                    }
                }
            });

        activity()->useLog('group_fees')
            ->performedOn($semester)
            ->causedBy(auth()->user())
            ->withProperties([
                'base_fee' => $baseRateTotal,
                'semester_id' => $semester->id,
                'updated_groups' => $updatedGroupCount,
            ])
            ->log('Updated group fees during semester bulk update');
    }

    private function getRoleRates(Semester $semester, ThesisRatesType $type, string $roleColumn, ?string $roleValue): int
    {
        return $semester->rates()
            ->where('type', $type->value)
            ->where($roleColumn, $roleValue)
            ->sum('amount');
    }
}
