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

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Section $section;

    // --- Computed ---

    #[Computed]
    public function schedules()
    {
        return Schedule::where('section_id', $this->section->id)
            ->with('group')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    // --- Filament Actions ---

    public function createScheduleAction(): Action
    {
        return Action::make('createSchedule')
            ->label('Schedule this Section')
            ->modalWidth('lg')
            ->modalCloseButton(false)
            ->icon('heroicon-m-plus')
            ->color('primary')
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
                    ->schema([
                        Select::make('group_id')
                            ->label('Group')
                            ->options(fn () => $this->section->groups->pluck('name', 'id'))
                            ->required()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live(),
                    ])
                    ->minItems(1)
                    ->reorderableWithButtons()
                    ->addActionLabel('Add a Group')
                    ->helperText('The time will be distributed evenly among them.'),
            ])
            ->action(function (array $data): void {
                $start = Carbon::parse($data['date'].' '.$data['start_time']);
                $end = Carbon::parse($data['date'].' '.$data['end_time']);

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

<div class="bg-gray-50 py-8 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6 pb-4">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Class Schedules
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage and view chronologically ordered schedules for all groups in this section automatically bounded by the semester date ranges.
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                {{ $this->createScheduleAction }}
            </div>
        </div>

        <!-- Schedules List -->
        <div class="bg-white shadow sm:rounded-md overflow-hidden border border-gray-200">
            @if($this->schedules->count() > 0)
                <ul role="list" class="divide-y divide-gray-100">
                    @foreach($this->schedules as $schedule)
                        <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 sm:px-6">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">
                                        {{ $schedule->group ? $schedule->group->name : 'Unknown Group' }}
                                    </p>
                                    <p class="mt-1 flex text-xs leading-5 text-gray-500 items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $schedule->venue }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-x-4">
                                <div class="hidden sm:flex sm:flex-col sm:items-end">
                                    <p class="text-sm leading-6 text-gray-900">
                                        {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2 z-10 relative">
                                    {{ ($this->editScheduleAction)(['schedule' => $schedule->id]) }}
                                    {{ ($this->deleteScheduleAction)(['schedule' => $schedule->id]) }}
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No schedules</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new schedule for a group in this section.</p>
                </div>
            @endif
        </div>
        
        <x-filament-actions::modals />
    </div>
</div>
