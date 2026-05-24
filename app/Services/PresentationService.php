<?php

namespace App\Services;

use App\Enums\PanelistRole;
use App\Enums\PresentationStatus;
use App\Enums\PresentationType;
use App\Models\Group;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PresentationService
{
    public function create(array $data): Schedule
    {
        $date = Carbon::parse($data['date'])->toDateString();
        $startTime = Carbon::parse($date.' '.$data['start_time']);
        $endTime = Carbon::parse($date.' '.$data['end_time']);

        $presentationType = $this->resolvePresentationType($data['presentation_type']);

        $latestSchedule = $this->latestScheduleForPresentationType((int) $data['group_id'], $presentationType);

        if ($latestSchedule !== null && ! in_array($latestSchedule->status, [PresentationStatus::FAILED, PresentationStatus::REDEFENSE], true)) {
            activity('Presentation Scheduling')
                ->event('failed')
                ->causedBy(auth()->user())->log(sprintf(
                    'Attempted to create a "%s" schedule for Group ID %d, but the latest schedule has "%s" status.',
                    $presentationType->getLabel(),
                    $data['group_id'],
                    $this->presentationStatusLabel($latestSchedule->status),
                ));
            throw ValidationException::withMessages([
                'group' => sprintf(
                    'This group already has a "%s" schedule with "%s" status. Only failed or re-defense schedules can be created again for the same presentation type.',
                    $presentationType->getLabel(),
                    $this->presentationStatusLabel($latestSchedule->status),
                ),
            ]);

        }

        $this->ensureNoSchedulingConflict(
            venue: $data['venue'],
            date: $date,
            startTime: $startTime,
            endTime: $endTime,
        );

        return Schedule::create([
            'section_id' => $data['section_id'],
            'group_id' => $data['group_id'],
            'venue' => $data['venue'],
            'date' => $date,
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'presentation_type' => $this->resolvePresentationType($data['presentation_type'])->value,
            'status' => $this->resolvePresentationStatus($data['status'] ?? PresentationStatus::SCHEDULED)->value,
            'panelists' => $this->normalizePanelists($data['panelists'] ?? null),
        ]);

    }

    public function update(array $data, Schedule $schedule): Schedule
    {
        $date = Carbon::parse($data['date'])->toDateString();
        $startTime = Carbon::parse($date.' '.$data['start_time']);
        $endTime = Carbon::parse($date.' '.$data['end_time']);

        $this->ensureNoSchedulingConflict(
            venue: $data['venue'],
            date: $date,
            startTime: $startTime,
            endTime: $endTime,
            ignoreScheduleId: $schedule->id,
        );

        $schedule->update([
            'date' => $date,
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'venue' => $data['venue'],
            'panelists' => $this->normalizePanelists($data['panelists'] ?? null),
        ]);

        return $schedule;
    }

    /**
     * @return array{scheduled: Collection<int, Schedule>, skipped: Collection<int, Group>, messages: Collection<int, string>}
     */
    public function bulkSchedule(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $sectionId = (int) $data['section_id'];
            $presentationType = $this->resolvePresentationType($data['presentation_type']);
            $presentationStatus = $this->resolvePresentationStatus($data['status'] ?? PresentationStatus::SCHEDULED);
            $date = Carbon::parse($data['date'])->toDateString();
            $currentStartTime = Carbon::parse($date.' '.$data['start_time']);
            $slotMinutes = (int) ($data['slot_minutes'] ?? 60);
            $gapMinutes = (int) ($data['gap_minutes'] ?? 0);
            $groupIds = $data['group_ids'] ?? null;

            /** @var Collection<int, Group> $groups */
            $groups = Group::query()
                ->where('section_id', $sectionId)
                ->when(is_array($groupIds) && $groupIds !== [], function ($query) use ($groupIds) {
                    $query->whereIn('id', $groupIds);
                })
                ->orderBy('name')
                ->get();

            $scheduledGroups = collect();
            $skippedGroups = collect();
            $messages = collect();

            foreach ($groups as $group) {
                $latestSchedule = $this->latestScheduleForPresentationType($group->id, $presentationType);

                if ($latestSchedule !== null && ! in_array($latestSchedule->status, [PresentationStatus::FAILED, PresentationStatus::REDEFENSE], true)) {
                    $skippedGroups->push($group);
                    $messages->push(sprintf(
                        'This group already has a "%s" schedule with "%s" status. Only failed or re-defense schedules can be created again for the same presentation type.',
                        $presentationType->getLabel(),
                        $this->presentationStatusLabel($latestSchedule->status),
                    ));

                    $currentStartTime->addMinutes($slotMinutes + $gapMinutes);

                    continue;
                }

                $endTime = $currentStartTime->copy()->addMinutes($slotMinutes);

                if ($this->hasSchedulingConflict(
                    venue: $data['venue'],
                    date: $date,
                    startTime: $currentStartTime,
                    endTime: $endTime,
                )) {
                    $skippedGroups->push($group);

                    $currentStartTime->addMinutes($slotMinutes + $gapMinutes);

                    continue;
                }

                $scheduledGroups->push(Schedule::create([
                    'section_id' => $sectionId,
                    'group_id' => $group->id,
                    'venue' => $data['venue'],
                    'date' => $date,
                    'start_time' => $currentStartTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'presentation_type' => $presentationType->value,
                    'status' => $presentationStatus->value,
                    'panelists' => $this->normalizePanelists($data['panelists'] ?? null),
                ]));

                $currentStartTime->addMinutes($slotMinutes + $gapMinutes);
            }

            return [
                'scheduled' => $scheduledGroups,
                'skipped' => $skippedGroups,
                'messages' => $messages,
            ];
        });
    }

    private function resolvePresentationType(PresentationType|string $presentationType): PresentationType
    {
        return $presentationType instanceof PresentationType
            ? $presentationType
            : PresentationType::from($presentationType);
    }

    private function resolvePresentationStatus(PresentationStatus|string $status): PresentationStatus
    {
        return $status instanceof PresentationStatus
            ? $status
            : PresentationStatus::from($status);
    }

    /**
     * @param  array<int|string, mixed>|null  $panelists
     * @return array<int|string, string>|null
     */
    private function normalizePanelists(?array $panelists): ?array
    {
        if ($panelists === null || $panelists === []) {
            return null;
        }

        $normalized = [];

        foreach ($panelists as $key => $panelist) {
            if (is_array($panelist)) {
                $panelistId = $panelist['id'] ?? null;
                $panelistRole = $panelist['role'] ?? null;

                if ($panelistId === null || $panelistRole === null) {
                    continue;
                }

                $normalized[(string) $panelistId] = $this->resolvePanelistRole($panelistRole)->value;

                continue;
            }

            if (is_int($key) || ctype_digit((string) $key)) {
                $normalized[(string) $panelist] = $this->defaultPanelistRole()->value;

                continue;
            }

            $normalized[(string) $key] = $this->resolvePanelistRole($panelist)->value;
        }

        return $normalized === [] ? null : $normalized;
    }

    private function defaultPanelistRole(): PanelistRole
    {
        return PanelistRole::MEMBER;
    }

    private function resolvePanelistRole(PanelistRole|string $panelistRole): PanelistRole
    {
        return $panelistRole instanceof PanelistRole
            ? $panelistRole
            : PanelistRole::from($panelistRole);
    }

    private function latestScheduleForPresentationType(int $groupId, PresentationType $presentationType): ?Schedule
    {
        return Schedule::query()
            ->where('group_id', $groupId)
            ->where('presentation_type', $presentationType->value)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->first();
    }

    // private function hasBlockingScheduleForPresentationType(int $groupId, PresentationType $presentationType): bool
    // {
    //     $latestSchedule = $this->latestScheduleForPresentationType($groupId, $presentationType);

    //     if ($latestSchedule === null) {
    //         return false;
    //     }

    //     return ! in_array(
    //         $latestSchedule->status,
    //         [PresentationStatus::FAILED, PresentationStatus::REDEFENSE],
    //         true,
    //     );
    // }

    private function presentationStatusLabel(PresentationStatus $status): string
    {
        return match ($status) {
            PresentationStatus::PASSED => 'Passed',
            PresentationStatus::REDEFENSE => 'Re-defense',
            PresentationStatus::FAILED => 'Failed',
            PresentationStatus::SCHEDULED => 'Scheduled',
        };
    }

    private function ensureNoSchedulingConflict(
        string $venue,
        string $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $ignoreScheduleId = null,
    ): void {
        if ($this->hasSchedulingConflict($venue, $date, $startTime, $endTime, $ignoreScheduleId)) {

            activity('Presentation Scheduling')
                ->event('failed')
                ->causedBy(auth()->user())->log(sprintf(
                    'Attempted to create/update a schedule on %s at %s-%s in venue "%s", but it conflicts with an existing schedule.',
                    $date,
                    $startTime->format('H:i'),
                    $endTime->format('H:i'),
                    $venue,
                ));

            throw ValidationException::withMessages([
                'date' => 'The selected schedule conflicts with an existing schedule.',
            ]);

        }
    }

    private function hasSchedulingConflict(
        string $venue,
        string $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $ignoreScheduleId = null,
    ): bool {
        return Schedule::query()
            ->whereDate('date', $date)
            ->where('venue', $venue)
            ->when($ignoreScheduleId !== null, fn ($query) => $query->where('id', '!=', $ignoreScheduleId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime->format('H:i:s'))
                    ->where('end_time', '>', $startTime->format('H:i:s'));
            })
            ->exists();
    }
}
