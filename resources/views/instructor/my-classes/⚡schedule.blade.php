<?php

use App\Enums\PresentationStatus;
use App\Enums\PresentationType;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
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
        return Schedule::where('section_id', $this->section->id)->with('group')->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get()->groupBy(fn($schedule) => $schedule->presentation_type?->value ?? '');
    }

    // --- Livewire Methods ---

    public function openStatusModal(int $scheduleId): void
    {
        $this->mountAction('updateStatus', ['schedule' => $scheduleId]);
    }

    // --- Filament Actions ---

    public function createScheduleAction(): Action
    {
        return Action::make('createSchedule')
            ->color('info')
            ->label('Schedule this Section')
            ->modalWidth('xl')
            ->modalCloseButton(false)
            ->icon('heroicon-m-plus')
            ->form([
                Grid::make(2)->schema([
                    DatePicker::make('date')
                        ->required()
                        ->minDate(now()->startOfDay())
                        ->maxDate($this->section->semester->end_date)
                        ->helperText('Must be within the active semester time range.')
                        ->columnSpanFull(),
                    TimePicker::make('start_time')->seconds(false)->required(),
                    TimePicker::make('end_time')->seconds(false)->required()->after('start_time'),
                    TextInput::make('venue')->required()->maxLength(255),
                    Select::make('presentation_type')->options(PresentationType::class),
                    Repeater::make('presenting_groups')
                        ->label('Presentation Order')
                        ->schema([Select::make('group_id')->label('Group')->options(fn() => $this->section->groups->pluck('name', 'id'))->required()->distinct()->disableOptionsWhenSelectedInSiblingRepeaterItems()->live()])
                        ->minItems(1)
                        ->reorderableWithButtons()
                        ->addActionLabel('Add a Group')
                        ->helperText('The time will be distributed evenly among them.')
                        ->columnSpanFull(),
                ]),
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
                        'presentation_type' => $data['presentation_type'],
                        'status' => PresentationStatus::SCHEDULED,
                    ]);
                }

                Notification::make()->title('Section schedule saved successfully.')->success()->send();
            });
    }

    public function editScheduleAction(): Action
    {
        return Action::make('editSchedule')
            ->modalWidth('xl')
            ->modalCloseButton(false)
            ->iconButton()
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->schema([
                Grid::make(2)->schema([
                    DatePicker::make('date')
                        ->required()
                        ->minDate(now()->startOfDay())
                        ->maxDate($this->section->semester->end_date)
                        ->columnSpanFull(),
                    TimePicker::make('start_time')->required(),
                    TimePicker::make('end_time')->required()->after('start_time'),
                    TextInput::make('venue')->required()->maxLength(255)->columnSpanFull(),
                    Select::make('panelists')
                        ->label('Panelists')
                        ->multiple()
                        ->searchable()
                        ->options(Instructor::query()->orderBy('last_name')->get()->mapWithKeys(fn(Instructor $i) => [$i->id => $i->full_name]))
                        ->columnSpanFull(),
                ]),
            ])
            ->fillForm(function (array $arguments): array {
                $schedule = Schedule::findOrFail($arguments['schedule']);

                return [
                    'date' => $schedule->date,
                    'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
                    'venue' => $schedule->venue,
                    'panelists' => $schedule->panelists ?? [],
                ];
            })
            ->action(function (array $data, array $arguments): void {
                $schedule = Schedule::findOrFail($arguments['schedule']);
                $schedule->update([
                    'date' => $data['date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'venue' => $data['venue'],
                    'panelists' => $data['panelists'] ?? [],
                ]);

                Notification::make()->title('Schedule updated successfully.')->success()->send();
            });
    }

    public function updateStatusAction(): Action
    {
        return Action::make('updateStatus')
            ->modalWidth('md')
            ->modalHeading('Presentation Details')
            ->modalCloseButton(false)
            ->schema([Grid::make(2)->schema([TextInput::make('_group')->label('Group')->disabled()->dehydrated(false), TextInput::make('_type')->label('Presentation Type')->disabled()->dehydrated(false), TextInput::make('_date')->label('Date')->disabled()->dehydrated(false), TextInput::make('_time')->label('Time')->disabled()->dehydrated(false), TextInput::make('_venue')->label('Venue')->disabled()->dehydrated(false)->columnSpanFull(), TextInput::make('_panelists')->label('Panelists')->disabled()->dehydrated(false)->columnSpanFull(), Select::make('status')->label('Update Status')->options(PresentationStatus::class)->required()->columnSpanFull()])])
            ->fillForm(function (array $arguments): array {
                $schedule = Schedule::with('group')->findOrFail($arguments['schedule']);
                $panelists = $schedule->panelists ?? [];
                $panelistNames = count($panelists) ? Instructor::whereIn('id', $panelists)->orderBy('last_name')->get()->map(fn(Instructor $i) => $i->full_name)->join(', ') : 'None assigned';

                return [
                    '_group' => $schedule->group?->name ?? 'Unknown Group',
                    '_type' => $schedule->presentation_type?->getLabel() ?? '—',
                    '_date' => Carbon::parse($schedule->date)->format('M d, Y'),
                    '_time' => Carbon::parse($schedule->start_time)->format('h:i A') . ' – ' . Carbon::parse($schedule->end_time)->format('h:i A'),
                    '_venue' => $schedule->venue,
                    '_panelists' => $panelistNames,
                    'status' => $schedule->status?->value,
                ];
            })
            ->action(function (array $data, array $arguments): void {
                Schedule::findOrFail($arguments['schedule'])->update(['status' => $data['status']]);
                Notification::make()->title('Status updated successfully.')->success()->send();
            });
    }

    public function deleteScheduleAction(): Action
    {
        return Action::make('deleteSchedule')
            ->modalWidth('lg')
            ->iconButton()
            ->modalCloseButton(false)
            ->icon('heroicon-m-trash')
            ->color('danger')
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

    {{-- Schedule sections grouped by presentation type --}}
    @if ($this->schedules->isNotEmpty())
        <div class="space-y-7">
            @foreach (PresentationType::cases() as $type)
                @php
                    $typeSchedules = $this->schedules->get($type->value, collect());
                    $style = match ($type) {
                        PresentationType::THESIS_A_PROPOSAL => [
                            'accent' => '#0052FF',
                            'bg' => 'rgba(0,82,255,0.06)',
                            'border' => 'rgba(0,82,255,0.18)',
                            'bar' => 'linear-gradient(to bottom, #0052FF, #4D7CFF)',
                        ],
                        PresentationType::THESIS_A_ORAL => [
                            'accent' => '#7C3AED',
                            'bg' => 'rgba(124,58,237,0.06)',
                            'border' => 'rgba(124,58,237,0.2)',
                            'bar' => 'linear-gradient(to bottom, #7C3AED, #A78BFA)',
                        ],
                        PresentationType::THESIS_A_MOCK => [
                            'accent' => '#D97706',
                            'bg' => 'rgba(217,119,6,0.06)',
                            'border' => 'rgba(217,119,6,0.2)',
                            'bar' => 'linear-gradient(to bottom, #D97706, #F59E0B)',
                        ],
                        PresentationType::THESIS_A_FINAL => [
                            'accent' => '#059669',
                            'bg' => 'rgba(5,150,105,0.06)',
                            'border' => 'rgba(5,150,105,0.2)',
                            'bar' => 'linear-gradient(to bottom, #059669, #34D399)',
                        ],
                        PresentationType::THESIS_B_ORAL => [
                            'accent' => '#4F46E5',
                            'bg' => 'rgba(79,70,229,0.06)',
                            'border' => 'rgba(79,70,229,0.2)',
                            'bar' => 'linear-gradient(to bottom, #4F46E5, #818CF8)',
                        ],
                        PresentationType::THESIS_B_MOCK => [
                            'accent' => '#DB2777',
                            'bg' => 'rgba(219,39,119,0.06)',
                            'border' => 'rgba(219,39,119,0.2)',
                            'bar' => 'linear-gradient(to bottom, #DB2777, #F472B6)',
                        ],
                        PresentationType::THESIS_B_FINAL => [
                            'accent' => '#0D9488',
                            'bg' => 'rgba(13,148,136,0.06)',
                            'border' => 'rgba(13,148,136,0.2)',
                            'bar' => 'linear-gradient(to bottom, #0D9488, #2DD4BF)',
                        ],
                    };
                @endphp

                @if ($typeSchedules->isNotEmpty())
                    <div>
                        {{-- Type section header --}}
                        <div class="mb-3 flex items-center gap-3">
                            <div class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5"
                                style="background: {{ $style['bg'] }}; border: 1px solid {{ $style['border'] }}">
                                <span class="h-2 w-2 rounded-full" style="background: {{ $style['accent'] }}"></span>
                                <span class="text-xs font-semibold tracking-wide"
                                    style="color: {{ $style['accent'] }}">{{ $type->getLabel() }}</span>
                            </div>
                            <div class="h-px flex-1" style="background: #E2E8F0"></div>
                            <span class="text-xs text-slate-400">{{ $typeSchedules->count() }}
                                {{ \Str::plural('slot', $typeSchedules->count()) }}</span>
                        </div>

                        {{-- Cards --}}
                        <div class="space-y-2">
                            @foreach ($typeSchedules as $schedule)
                                @php
                                    $statusStyle = match ($schedule->status) {
                                        PresentationStatus::PASSED => [
                                            'bg' => 'rgba(5,150,105,0.08)',
                                            'border' => 'rgba(5,150,105,0.2)',
                                            'color' => '#059669',
                                            'label' => 'Passed',
                                        ],
                                        PresentationStatus::REDEFENSE => [
                                            'bg' => 'rgba(217,119,6,0.08)',
                                            'border' => 'rgba(217,119,6,0.2)',
                                            'color' => '#D97706',
                                            'label' => 'Re-defense',
                                        ],
                                        PresentationStatus::FAILED => [
                                            'bg' => 'rgba(239,68,68,0.08)',
                                            'border' => 'rgba(239,68,68,0.2)',
                                            'color' => '#DC2626',
                                            'label' => 'Failed',
                                        ],
                                        default => [
                                            'bg' => 'rgba(100,116,139,0.07)',
                                            'border' => 'rgba(100,116,139,0.15)',
                                            'color' => '#64748B',
                                            'label' => 'Scheduled',
                                        ],
                                    };
                                    $panelists = $schedule->panelists ?? [];
                                    $panelistNames = count($panelists)
                                        ? Instructor::whereIn('id', $panelists)
                                            ->orderBy('last_name')
                                            ->pluck('last_name', 'first_name')
                                            ->map(fn($ln, $fn) => $fn . ' ' . $ln)
                                            ->values()
                                        : collect();
                                @endphp
                                <div wire:click="openStatusModal({{ $schedule->id }})"
                                    class="group relative cursor-pointer rounded-xl border border-slate-200 bg-white transition-all duration-200 hover:border-slate-300 hover:shadow-md">
                                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl"
                                        style="background: {{ $style['bar'] }}"></div>
                                    <div class="flex items-start justify-between gap-4 py-4 pl-5 pr-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="text-[0.9375rem] font-semibold text-slate-900">
                                                    {{ $schedule->group ? $schedule->group->name : 'Unknown Group' }}
                                                </p>
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                    style="background: {{ $statusStyle['bg'] }}; border: 1px solid {{ $statusStyle['border'] }}; color: {{ $statusStyle['color'] }}">
                                                    {{ $statusStyle['label'] }}
                                                </span>
                                            </div>
                                            <div
                                                class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    {{ $schedule->venue }}
                                                </span>
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                                                </span>
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                                    –
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                                </span>
                                            </div>
                                            @if ($panelistNames->isNotEmpty())
                                                <div class="mt-2 flex flex-wrap gap-1.5">
                                                    @foreach ($panelistNames as $name)
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">{{ $name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex shrink-0 items-center gap-1" @click.stop>
                                            {{ ($this->editScheduleAction)(['schedule' => $schedule->id]) }}
                                            {{ ($this->deleteScheduleAction)(['schedule' => $schedule->id]) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Uncategorized (no presentation type set) --}}
            @php $untyped = $this->schedules->get('', collect()); @endphp
            @if ($untyped->isNotEmpty())
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5"
                            style="background: rgba(148,163,184,0.08); border: 1px solid rgba(148,163,184,0.2)">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            <span class="text-xs font-semibold tracking-wide text-slate-500">Uncategorized</span>
                        </div>
                        <div class="h-px flex-1" style="background: #E2E8F0"></div>
                        <span class="text-xs text-slate-400">{{ $untyped->count() }}
                            {{ \Str::plural('slot', $untyped->count()) }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($untyped as $schedule)
                            @php
                                $statusStyle = match ($schedule->status) {
                                    PresentationStatus::PASSED => [
                                        'bg' => 'rgba(5,150,105,0.08)',
                                        'border' => 'rgba(5,150,105,0.2)',
                                        'color' => '#059669',
                                        'label' => 'Passed',
                                    ],
                                    PresentationStatus::REDEFENSE => [
                                        'bg' => 'rgba(217,119,6,0.08)',
                                        'border' => 'rgba(217,119,6,0.2)',
                                        'color' => '#D97706',
                                        'label' => 'Re-defense',
                                    ],
                                    PresentationStatus::FAILED => [
                                        'bg' => 'rgba(239,68,68,0.08)',
                                        'border' => 'rgba(239,68,68,0.2)',
                                        'color' => '#DC2626',
                                        'label' => 'Failed',
                                    ],
                                    default => [
                                        'bg' => 'rgba(100,116,139,0.07)',
                                        'border' => 'rgba(100,116,139,0.15)',
                                        'color' => '#64748B',
                                        'label' => 'Scheduled',
                                    ],
                                };
                                $panelists = $schedule->panelists ?? [];
                                $panelistNames = count($panelists)
                                    ? Instructor::whereIn('id', $panelists)
                                        ->orderBy('last_name')
                                        ->pluck('last_name', 'first_name')
                                        ->map(fn($ln, $fn) => $fn . ' ' . $ln)
                                        ->values()
                                    : collect();
                            @endphp
                            <div wire:click="openStatusModal({{ $schedule->id }})"
                                class="group relative cursor-pointer rounded-xl border border-slate-200 bg-white transition-all duration-200 hover:border-slate-300 hover:shadow-md">
                                <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl bg-slate-300"></div>
                                <div class="flex items-start justify-between gap-4 py-4 pl-5 pr-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-[0.9375rem] font-semibold text-slate-900">
                                                {{ $schedule->group ? $schedule->group->name : 'Unknown Group' }}
                                            </p>
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                style="background: {{ $statusStyle['bg'] }}; border: 1px solid {{ $statusStyle['border'] }}; color: {{ $statusStyle['color'] }}">
                                                {{ $statusStyle['label'] }}
                                            </span>
                                        </div>
                                        <div
                                            class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $schedule->venue }}
                                            </span>
                                            <span class="flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                                            </span>
                                            <span class="flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} –
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                            </span>
                                        </div>
                                        @if ($panelistNames->isNotEmpty())
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach ($panelistNames as $name)
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">{{ $name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 items-center gap-1" @click.stop>
                                        {{ ($this->editScheduleAction)(['schedule' => $schedule->id]) }}
                                        {{ ($this->deleteScheduleAction)(['schedule' => $schedule->id]) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
