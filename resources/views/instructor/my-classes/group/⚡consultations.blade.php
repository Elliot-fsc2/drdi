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

        Textarea::make('remarks')
          ->label('Notes/Remarks')
          ->rows(3)
          ->placeholder('Add any notes or agenda for this consultation...')
          ->columnSpanFull(),
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

        Textarea::make('remarks')
          ->label('Notes/Remarks')
          ->rows(3)
          ->placeholder('Add any notes or agenda for this consultation...')
          ->columnSpanFull(),
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

<div class="p-4">
  <div class="mb-4 flex items-center justify-between">
    <h3 class="font-semibold text-slate-900">Consultation Sessions</h3>
    <x-filament::button wire:click="mountAction('scheduleConsultation')" class="bg-blue-600 text-white">
      Schedule Consultation
    </x-filament::button>
  </div>

  @if($this->consultations->isEmpty())
    <div class="text-center py-12 bg-white border border-slate-200 rounded-lg">
      <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-slate-900">No consultations scheduled</h3>
      <p class="mt-1 text-sm text-slate-500">Get started by scheduling your first consultation session.</p>
      <div class="mt-6">
        <x-filament::button wire:click="mountAction('scheduleConsultation')" class="bg-blue-600 text-white">
          Schedule Consultation
        </x-filament::button>
      </div>
    </div>
  @else
    <div class="space-y-3">
      @foreach($this->consultations as $consultation)
        <div class="border border-slate-200 rounded-lg p-4 bg-white hover:border-blue-300 transition-colors">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h4 class="font-semibold text-slate-900">{{ $consultation['topic'] }}</h4>
                <span
                  class="px-2 py-0.5 text-xs font-medium rounded
                                                                              {{ $consultation['status'] === 'Completed' ? 'bg-green-100 text-green-700' : '' }}
                                                                              {{ $consultation['status'] === 'Scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                                                                              {{ $consultation['status'] === 'Cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                  {{ $consultation['status'] }}
                </span>
              </div>

              <div class="flex items-center gap-4 text-sm text-slate-600 mb-2">
                <div class="flex items-center gap-1">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ $consultation['date'] }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>{{ $consultation['time'] }} ({{ $consultation['duration'] }})</span>
                </div>
                @if($consultation['status'] === 'Completed')
                  <div class="flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                @endif
              </div>

              @if($consultation['notes'])
                <div class="mt-3 pt-3 border-t border-slate-100">
                  <p class="text-sm text-slate-700">
                    <span class="font-medium text-slate-900">Notes:</span> {{ $consultation['notes'] }}
                  </p>
                </div>
              @endif
            </div>

            <x-filament::dropdown placement="bottom-end">
              <x-slot name="trigger">
                <button class="p-1 hover:bg-slate-100 rounded transition-colors">
                  <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
              </x-slot>

              <x-filament::dropdown.list>
                <x-filament::dropdown.list.item icon="heroicon-o-pencil-square"
                  wire:click="mountAction('editConsultation', { consultationId: {{ $consultation['id'] }} })">
                  Edit Consultation
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                  wire:click="mountAction('deleteConsultation', { consultationId: {{ $consultation['id'] }} })">
                  Delete Consultation
                </x-filament::dropdown.list.item>
              </x-filament::dropdown.list>
            </x-filament::dropdown>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <x-filament-actions::modals />
</div>