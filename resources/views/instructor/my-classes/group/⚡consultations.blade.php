<?php

use App\Models\Consultation;
use App\Models\Group;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Group $group;

    public Section $section;

    public function mount()
    {
        abort_if($this->group->section->instructor_id !== auth()->user()->profileable->id, 403);
        abort_if($this->group->section_id !== $this->section->id, 403);
    }

    public function scheduleConsultationAction(): Action
    {
        return Action::make('scheduleConsultation')
            ->modalAutofocus(false)
            ->modalWidth('2xl')
            ->modalCloseButton(false)
            ->label('Schedule Consultation')
            ->icon(Heroicon::CalendarDays)
            ->modalHeading('Schedule Consultation Session')
            ->modalDescription('Create a new consultation session for this group.')
            ->form([
                Select::make('type')
                    ->label('Consultation Type')
                    ->options([
                        'Title Proposal Review' => 'Title Proposal Review',
                        'Research Methodology Discussion' => 'Research Methodology Discussion',
                        'Literature Review Progress' => 'Literature Review Progress',
                        'Data Analysis Review' => 'Data Analysis Review',
                        'Chapter Review' => 'Chapter Review',
                        'Defense Preparation' => 'Defense Preparation',
                        'General Consultation' => 'General Consultation',
                    ])
                    ->required()
                    ->searchable(),

                DatePicker::make('scheduled_at')
                    ->label('Schedule Date')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required()
                    ->minDate(now()->subWeeks(2)->startOfDay())
                    ->maxDate(now()->addWeeks(2)->endOfDay()),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('scheduled')
                    ->required(),

                Textarea::make('remarks')->label('Notes/Remarks')->rows(3)->placeholder('Add any notes or agenda for this consultation...')->columnSpanFull(),
            ])
            ->successNotificationTitle('Consultation scheduled successfully')
            ->action(function (array $data): void {
                Consultation::create([
                    'group_id' => $this->group->id,
                    'instructor_id' => auth()->user()->profileable->id,
                    'type' => $data['type'],
                    'scheduled_at' => $data['scheduled_at'],
                    'status' => $data['status'],
                    'remarks' => $data['remarks'] ?? null,
                ]);

                unset($this->consultations);
            });
    }

    public function editConsultationAction(): Action
    {
        return Action::make('editConsultation')
            ->modalAutofocus(false)
            ->modalWidth('2xl')
            ->modalCloseButton(false)
            ->icon(Heroicon::PencilSquare)
            ->modalHeading('Edit Consultation')
            ->fillForm(function (array $arguments): array {
                $consultation = Consultation::findOrFail($arguments['consultationId']);

                return [
                    'type' => $consultation->type,
                    'scheduled_at' => $consultation->scheduled_at,
                    'status' => $consultation->status,
                    'remarks' => $consultation->remarks,
                ];
            })
            ->form([
                Select::make('type')
                    ->label('Consultation Type')
                    ->options([
                        'Title Proposal Review' => 'Title Proposal Review',
                        'Research Methodology Discussion' => 'Research Methodology Discussion',
                        'Literature Review Progress' => 'Literature Review Progress',
                        'Data Analysis Review' => 'Data Analysis Review',
                        'Chapter Review' => 'Chapter Review',
                        'Defense Preparation' => 'Defense Preparation',
                        'General Consultation' => 'General Consultation',
                    ])
                    ->required()
                    ->searchable(),

                DatePicker::make('scheduled_at')
                    ->label('Schedule Date')
                    ->closeOnDateSelection()
                    ->native(false)
                    ->required()
                    ->minDate(now()->subWeeks(2)->startOfDay())
                    ->maxDate(now()->addWeeks(2)->endOfDay()),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),

                Textarea::make('remarks')->label('Notes/Remarks')->rows(3)->placeholder('Add any notes or agenda for this consultation...')->columnSpanFull(),
            ])
            ->successNotificationTitle('Consultation updated successfully')
            ->action(function (array $arguments, array $data): void {
                $consultation = Consultation::findOrFail($arguments['consultationId']);
                $consultation->update($data);

                unset($this->consultations);
            });
    }

    public function deleteConsultationAction(): Action
    {
        return Action::make('deleteConsultation')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->modalHeading('Delete Consultation')
            ->modalDescription('Are you sure you want to delete this consultation session? This action cannot be undone.')
            ->modalSubmitActionLabel('Yes, Delete')
            ->color('danger')
            ->icon(Heroicon::Trash)
            ->successNotificationTitle('Consultation deleted successfully')
            ->action(function (array $arguments): void {
                $consultation = Consultation::findOrFail($arguments['consultationId']);
                $consultation->delete();

                unset($this->consultations);
            });
    }

    #[Computed]
    public function consultations()
    {
        return Consultation::where('group_id', $this->group->id)
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($consultation) {
                return [
                    'id' => $consultation->id,
                    'topic' => $consultation->type,
                    'status' => ucfirst($consultation->status),
                    'date' => $consultation->scheduled_at->format('M d, Y'),
                    'time' => $consultation->scheduled_at->format('h:i A'),
                    'duration' => '1 hr', // Placeholder, you can calculate actual duration if needed
                    'notes' => $consultation->remarks,
                ];
            });
    }
};
?>

<div class="space-y-4 p-4 md:p-5">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <p class="text-[0.8125rem] font-medium text-slate-500">Consultation Sessions</p>
        <x-filament::button wire:click="mountAction('scheduleConsultation')" color="info">
            Schedule Consultation
        </x-filament::button>
    </div>

    @if ($this->consultations->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-10 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="mb-1 font-semibold text-slate-700">No consultations scheduled</p>
            <p class="text-sm text-slate-400">Get started by scheduling your first consultation session.</p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($this->consultations as $consultation)
                @php
                    $statusConfig = match ($consultation['status']) {
                        'Completed' => [
                            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'dot' => 'bg-emerald-500',
                            'stripe' => 'bg-emerald-500',
                        ],
                        'Cancelled' => [
                            'badge' => 'border-red-200 bg-red-50 text-red-700',
                            'dot' => 'bg-red-400',
                            'stripe' => 'bg-red-400',
                        ],
                        default => [
                            'badge' => 'border-blue-200 bg-blue-50 text-blue-700',
                            'dot' => 'bg-blue-500',
                            'stripe' => '',
                        ],
                    };
                @endphp
                <div
                    class="relative rounded-xl border border-slate-200 bg-white transition-all duration-200 hover:border-blue-200 hover:shadow-md">
                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl {{ $statusConfig['stripe'] }}"
                        @if (!$statusConfig['stripe']) style="background: linear-gradient(to bottom, #0052FF, #4D7CFF);" @endif>
                    </div>
                    <div class="py-4 pl-5 pr-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                    <h4 class="text-[0.9375rem] font-semibold text-slate-900">
                                        {{ $consultation['topic'] }}</h4>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['badge'] }}">
                                        <span class="h-1 w-1 rounded-full {{ $statusConfig['dot'] }}"></span>
                                        {{ $consultation['status'] }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $consultation['date'] }}
                                    </span>
                                </div>
                                @if ($consultation['notes'])
                                    <p class="mt-2 border-t border-slate-100 pt-2 text-xs text-slate-500">
                                        <span class="font-medium text-slate-600">Notes:</span>
                                        {{ $consultation['notes'] }}
                                    </p>
                                @endif
                            </div>
                            <x-filament::dropdown placement="bottom-end">
                                <x-slot name="trigger">
                                    <button class="shrink-0 rounded-lg p-1.5 transition-colors hover:bg-slate-100">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-filament::dropdown.list>
                                    <x-filament::dropdown.list.item icon="heroicon-o-pencil-square"
                                        wire:click="mountAction('editConsultation', { consultationId: {{ $consultation['id'] }} })">
                                        Edit
                                    </x-filament::dropdown.list.item>
                                    <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                                        wire:click="mountAction('deleteConsultation', { consultationId: {{ $consultation['id'] }} })">
                                        Delete
                                    </x-filament::dropdown.list.item>
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</div>
