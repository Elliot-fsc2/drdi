<?php

use App\Models\Consultation;
use App\Models\Group;
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
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('View Group')]
  class extends Component implements HasActions, HasSchemas
  {
      use InteractsWithActions;
      use InteractsWithSchemas;

      public Group $group;

      #[Url]
      public string $tab = 'members';

      public function mount(): void
      {
          abort_unless(
              $this->group->personnel()->where('instructor_id', auth()->user()->profileable->id)->exists(),
              403
          );
      }

      #[Computed]
      public function routePrefix(): string
      {
          $user = auth()->user();
          $isRDO = $user->profileable_type === \App\Models\Instructor::class
            && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

          return $isRDO ? 'rdo' : 'instructor';
      }

      #[Computed]
      public function members()
      {
          return $this->group->members()
              ->select('students.id', 'students.first_name', 'students.last_name', 'students.student_number')
              ->orderByRaw('students.id = ? DESC', [$this->group->leader_id])
              ->get();
      }

      #[Computed]
      public function personnels()
      {
          return $this->group->personnel()
              ->with(['instructor.department'])
              ->get()
              ->map(function ($person) {
                  return [
                      'id' => $person->id,
                      'name' => "{$person->instructor->first_name} {$person->instructor->last_name}",
                      'department' => $person->instructor->department->name ?? 'N/A',
                      'role' => $person->role->name,
                  ];
              });
      }

      #[Computed]
      public function consultations()
      {
          return Consultation::where('group_id', $this->group->id)
              ->where('instructor_id', auth()->user()->profileable->id)
              ->orderBy('scheduled_at', 'desc')
              ->get()
              ->map(function ($consultation) {
                  return [
                      'id' => $consultation->id,
                      'topic' => $consultation->type,
                      'status' => ucfirst($consultation->status),
                      'date' => $consultation->scheduled_at->format('M d, Y'),
                      'time' => $consultation->scheduled_at->format('h:i A'),
                      'notes' => $consultation->remarks,
                  ];
              });
      }

      #[Computed]
      public function proposal()
      {
          return $this->group->proposal;
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

                  abort_unless($consultation->instructor_id === auth()->user()->profileable->id, 403);

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

                  abort_unless($consultation->instructor_id === auth()->user()->profileable->id, 403);

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

                  abort_unless($consultation->instructor_id === auth()->user()->profileable->id, 403);

                  $consultation->delete();

                  unset($this->consultations);
              });
      }
  };
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">{{ $this->group->name }}</x-slot>

<div class="p-0 lg:p-4 bg-slate-50 min-h-screen">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-4 md:mb-6 px-3 lg:px-0">
      <div class="flex items-center gap-2 text-xs md:text-sm text-slate-600 mb-3 pt-3 lg:pt-0">
        <a href="{{ route($this->routePrefix . '.groups') }}" wire:navigate class="hover:text-blue-600">Groups</a>
        <span>/</span>
        <span class="text-slate-900 font-medium">{{ $this->group->name }}</span>
      </div>

      <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-2xl md:text-3xl font-bold text-slate-900">{{ $this->group->name }}</h1>
          <p class="text-slate-600 mt-1 text-sm md:text-base">
            {{ $this->group->section->name }} • {{ $this->group->section->program->name }}
          </p>
        </div>

        <div class="flex items-center gap-2">
          <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">
            Assigned
          </span>
        </div>
      </div>
    </div>

    <!-- Main Content with Sidebar Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">

      <!-- Main Content Area -->
      <div class="lg:col-span-3">
        <!-- Tabs -->
        <div class="bg-white border-x-0 border-y border-slate-200 lg:border lg:rounded-lg overflow-hidden">
          <div class="border-b border-slate-200 overflow-x-auto">
            <div class="flex gap-4 md:gap-6 px-3 md:px-4 min-w-max md:min-w-0">
              <a href="?tab=members" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'members' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Members
              </a>
              <a href="?tab=personnel" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'personnel' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Personnel
              </a>
              <a href="?tab=consultation" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'consultation' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Consultations
              </a>
              <a href="?tab=proposal" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'proposal' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Proposed Title
              </a>
            </div>
          </div>

          <!-- Members Tab (read-only) -->
          @if($tab === 'members')
            <div class="p-3 md:p-4">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Group Members</h3>
                <span class="text-xs text-slate-400 italic">View only</span>
              </div>

              @if($this->members->isEmpty())
                <div class="text-center py-12 bg-white border border-slate-200 rounded-lg">
                  <x-heroicon-o-users class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                  <p class="text-sm font-medium text-slate-500">No members yet</p>
                </div>
              @else
                <div class="space-y-3">
                  @foreach($this->members as $member)
                    <div class="border border-slate-200 rounded-lg p-3 md:p-4 bg-white">
                      <div class="flex items-center gap-3 md:gap-4">
                        <div
                          class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm md:text-base shrink-0">
                          {{ substr($member->first_name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                          <h4 class="font-semibold text-slate-900 flex items-center gap-2 flex-wrap">
                            <span class="truncate">{{ $member->first_name }} {{ $member->last_name }}</span>
                            @if($member->id === $this->group->leader_id)
                              <span class="px-2 py-0.5 text-xs font-medium rounded bg-purple-100 text-purple-700 shrink-0">
                                Leader
                              </span>
                            @endif
                          </h4>
                          <p class="text-sm text-slate-600">{{ $member->student_number }}</p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          @endif

          <!-- Personnel Tab (read-only) -->
          @if($tab === 'personnel')
            <div class="p-3 md:p-4">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Assigned Personnel</h3>
                <span class="text-xs text-slate-400 italic">View only</span>
              </div>

              @if($this->personnels->isEmpty())
                <div class="text-center py-12 bg-white border border-slate-200 rounded-lg">
                  <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                  <p class="text-sm font-medium text-slate-500">No personnel assigned</p>
                </div>
              @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  @foreach($this->personnels as $person)
                    <div class="bg-white border border-slate-200 rounded-lg p-4">
                      <div class="flex items-center gap-3 mb-3">
                        <div
                          class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold shrink-0">
                          {{ substr($person['name'], 0, 1) }}
                        </div>
                        <div>
                          <h4 class="font-semibold text-slate-900">{{ $person['name'] }}</h4>
                          <p class="text-xs text-slate-500">{{ $person['department'] }}</p>
                        </div>
                      </div>
                      <div class="pt-3 border-t border-slate-100">
                        <span class="text-sm text-slate-700 font-medium">{{ $person['role'] }}</span>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          @endif

          <!-- My Consultations Tab (editable, own only) -->
          @if($tab === 'consultation')
            <div class="p-3 md:p-4">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Consultations</h3>
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
                              <span>{{ $consultation['time'] }}</span>
                            </div>
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
                  @endforeach
                </div>
              @endif
            </div>
          @endif

          <!-- Proposed Title Tab (read-only) -->
          @if($tab === 'proposal')
            <div class="p-3 md:p-4">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Proposed Title</h3>
                <span class="text-xs text-slate-400 italic">View only</span>
              </div>

              @if(!$this->proposal)
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-8 text-center">
                  <svg class="h-12 w-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <h4 class="text-base font-semibold text-slate-900 mb-1">No Proposal Yet</h4>
                  <p class="text-slate-500 text-sm">This group hasn't submitted a research title proposal.</p>
                </div>
              @else
                @php
                  $statusColors = [
                    'approved' => 'bg-green-100 text-green-700',
                    'pending' => 'bg-orange-100 text-orange-700',
                    'rejected' => 'bg-red-100 text-red-700',
                  ];
                @endphp
                <div class="bg-white border border-slate-200 rounded-lg p-6">
                  <div class="flex items-start justify-between gap-4 mb-3">
                    <h4 class="text-lg font-bold text-slate-900 flex-1">{{ $this->proposal->title }}</h4>
                    <span
                      class="px-3 py-1 text-xs font-medium rounded shrink-0 {{ $statusColors[$this->proposal->status] ?? 'bg-slate-100 text-slate-600' }}">
                      {{ ucfirst($this->proposal->status) }}
                    </span>
                  </div>

                  <p class="text-xs text-slate-500 mb-4">Submitted: {{ $this->proposal->created_at->format('M d, Y') }}</p>

                  @if($this->proposal->description)
                    <div class="mb-4">
                      <h5 class="text-sm font-semibold text-slate-700 mb-2">Description</h5>
                      <p class="text-slate-600 text-sm leading-relaxed">{{ $this->proposal->description }}</p>
                    </div>
                  @endif

                  @if($this->proposal->feedback)
                    <div class="pt-4 border-t border-slate-100">
                      <h5 class="text-sm font-semibold text-slate-700 mb-2">Feedback</h5>
                      <p class="text-slate-600 text-sm leading-relaxed">{{ $this->proposal->feedback }}</p>
                    </div>
                  @endif
                </div>
              @endif
            </div>
          @endif
        </div>
      </div>

      <!-- Info Sidebar -->
      <div class="lg:col-span-1">
        <div
          class="bg-white border-x-0 border-y border-slate-200 lg:border lg:rounded-lg p-4 md:p-5 lg:sticky lg:top-4">
          <h3 class="font-bold text-slate-900 mb-4">Group Overview</h3>

          <div class="space-y-4">
            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Leader</div>
              @if($this->group->leader)
                <div class="text-base font-semibold text-slate-900">
                  {{ $this->group->leader->first_name }} {{ $this->group->leader->last_name }}
                </div>
              @else
                <div class="text-base text-slate-400 italic">No leader assigned</div>
              @endif
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Members</div>
              <div class="text-3xl font-bold text-slate-900">{{ $this->members->count() }}</div>
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Course</div>
              <div class="text-base font-semibold text-slate-900">{{ $this->group->section->program->name }}</div>
            </div>

            <div>
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Section</div>
              <div class="text-base font-semibold text-slate-900">{{ $this->group->section->name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <x-filament-actions::modals />