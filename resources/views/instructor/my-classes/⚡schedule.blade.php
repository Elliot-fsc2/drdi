<?php

use App\Models\Schedule;
use App\Models\Section;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Section $section;

    // --- Computed ---

    #[Computed]
    public function schedules()
    {
        return Schedule::where('section_id', $this->section->id)->with('group')->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get();
    }

    // --- Filament Actions ---

    public function createScheduleAction(): Action
    {
        return Action::make('createSchedule')
            ->color('info')
            ->label('Schedule this Section')
            ->modalWidth('lg')
            ->modalCloseButton(false)
            ->icon('heroicon-m-plus')
            ->form([
                DatePicker::make('date')
                    ->required()
                    ->minDate(now()->startOfDay())
                    ->maxDate($this->section->semester->end_date)
                    ->helperText('Must be within the active semester time range.'),
                TimePicker::make('start_time')->required(),
                TimePicker::make('end_time')->required()->after('start_time'),
                TextInput::make('venue')->required()->maxLength(255),
                Repeater::make('presenting_groups')
                    ->label('Presentation Order')
                    ->schema([Select::make('group_id')->label('Group')->options(fn() => $this->section->groups->pluck('name', 'id'))->required()->distinct()->disableOptionsWhenSelectedInSiblingRepeaterItems()->live()])
                    ->minItems(1)
                    ->reorderableWithButtons()
                    ->addActionLabel('Add a Group')
                    ->helperText('The time will be distributed evenly among them.'),
            ])
            ->action(function (array $data): void {
                $start = Carbon::parse($data['date'] . ' ' . $data['start_time']);
                $end = Carbon::parse($data['date'] . ' ' . $data['end_time']);

                $totalMinutes = $start->diffInMinutes($end);
                $groupsCount = count($data['presenting_groups']);
                $minutesPerGroup = $groupsCount > 0 ? floor($totalMinutes / $groupsCount) : 0;

                foreach ($data['presenting_groups'] as $index => $groupItem) {
                    $groupId = $groupItem['group_id'];
                    $groupStart = $start->copy()->addMinutes($index * $minutesPerGroup);

                    // The last group takes any remainder minutes to ensure exact end time
                    if ($index == $groupsCount - 1) {
                        $groupEnd = $end;
                    } else {
                        $groupEnd = $groupStart->copy()->addMinutes($minutesPerGroup);
                    }

                    Schedule::create([
                        'section_id' => $this->section->id,
                        'group_id' => $groupId,
                        'date' => $data['date'],
                        'start_time' => $groupStart->format('H:i:s'),
                        'end_time' => $groupEnd->format('H:i:s'),
                        'venue' => $data['venue'],
                    ]);
                }

                Notification::make()->title('Section schedule saved successfully.')->success()->send();
            });
    }

    public function editScheduleAction(): Action
    {
        return Action::make('editSchedule')
            ->modalWidth('lg')
            ->modalCloseButton(false)
            ->label('Edit')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->button()
            ->outlined()
            ->form([
                DatePicker::make('date')
                    ->required()
                    ->minDate(now()->startOfDay())
                    ->maxDate($this->section->semester->end_date),
                TimePicker::make('start_time')->required(),
                TimePicker::make('end_time')->required()->after('start_time'),
                TextInput::make('venue')->required()->maxLength(255),
            ])
            ->fillForm(function (array $arguments): array {
                $schedule = Schedule::findOrFail($arguments['schedule']);

                return [
                    'date' => $schedule->date,
                    'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
                    'venue' => $schedule->venue,
                ];
            })
            ->action(function (array $data, array $arguments): void {
                $schedule = Schedule::findOrFail($arguments['schedule']);
                $schedule->update([
                    'date' => $data['date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'venue' => $data['venue'],
                ]);

                Notification::make()->title('Schedule updated successfully.')->success()->send();
            });
    }

    public function deleteScheduleAction(): Action
    {
        return Action::make('deleteSchedule')
            ->modalWidth('lg')
            ->modalCloseButton(false)
            ->label('Delete')
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->button()
            ->outlined()
            ->requiresConfirmation()
            ->action(function (array $arguments): void {
                Schedule::findOrFail($arguments['schedule'])->delete();
                Notification::make()->title('Schedule deleted successfully.')->success()->send();
            });
    }
};
?>


@assets
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="space-y-4 p-4 md:p-5">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[0.8125rem] font-medium text-slate-500">Class Schedules</p>
            <p class="mt-0.5 text-xs text-slate-400">Bounded by active semester date ranges &amp; distributed evenly
                across groups.</p>
        </div>
        {{ $this->createScheduleAction }}
    </div>

    {{-- Schedule list --}}
    @if ($this->schedules->count() > 0)
        <div class="space-y-2.5">
            @foreach ($this->schedules as $schedule)
                <div
                    class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white transition-all duration-200 hover:border-blue-200 hover:shadow-md">
                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl"
                        style="background: linear-gradient(to bottom, #0052FF, #4D7CFF);"></div>
                    <div class="flex items-center justify-between gap-4 py-4 pl-5 pr-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-[0.9375rem] font-semibold text-slate-900">
                                {{ $schedule->group ? $schedule->group->name : 'Unknown Group' }}
                            </p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $schedule->venue }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} –
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            {{ ($this->editScheduleAction)(['schedule' => $schedule->id]) }}
                            {{ ($this->deleteScheduleAction)(['schedule' => $schedule->id]) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-10 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="mb-1 font-semibold text-slate-700">No schedules yet</p>
            <p class="text-sm text-slate-400">Create a schedule to assign presentation slots for each group.</p>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
