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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">{{ $this->group->name }}</x-slot>

<div class="min-h-screen relative" style="background: #F8FAFC">

  {{-- Ambient glows --}}
  <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
      style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
    <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
      style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">

    {{-- ── Page Header ──────────────────────────────────────────────────────────────── --}}
    <div class="mb-8 sm:mb-10">

      {{-- Breadcrumb --}}
      <div class="flex items-center gap-2 mb-5 text-sm" style="color: #94A3B8">
        <a href="{{ route($this->routePrefix . '.groups') }}" wire:navigate
          class="transition-colors duration-150 hover:text-blue-500 font-medium">
          Groups
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
            clip-rule="evenodd" />
        </svg>
        <span style="color: #0F172A; font-weight: 600">{{ $this->group->name }}</span>
      </div>

      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
        <div>
          {{-- Assigned badge --}}
          <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-4"
            style="border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.07)">
            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #F59E0B"></span>
            <span
              style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #B45309; text-transform: uppercase">
              Assigned Adviser
            </span>
          </div>

          <h1 class="leading-tight"
            style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
            {{ $this->group->name }}
          </h1>
          <p class="mt-2 text-sm" style="color: #64748B">
            {{ $this->group->section->name }} &bull; {{ $this->group->section->program->name }}
          </p>
        </div>

        {{-- Tab switcher --}}
        <div class="inline-flex items-center gap-1 rounded-xl p-1 shrink-0 flex-wrap"
          style="background: #EEF2FF; border: 1px solid rgba(0,82,255,0.12)">
          @foreach([['members','Members'],['personnel','Personnel'],['consultation','Consultations'],['proposal','Proposal']] as [$key,$label])
            <a href="?tab={{ $key }}" wire:navigate
              class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap"
              style="{{ $tab === $key ? 'background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.3)' : 'color: #64748B' }}">
              {{ $label }}
            </a>
          @endforeach
        </div>
      </div>
    </div>

    {{-- ── Two-column layout ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 lg:gap-6">

      {{-- ── Main panel ──────────────────────────────────────────────────────────────── --}}
      <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

          {{-- Gradient top stripe --}}
          <div class="h-[3px]" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

          {{-- ── Members ─────────────────────────────────────────────────────────────── --}}
          @if($tab === 'members')
            <div class="p-5 sm:p-6">
              <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-base" style="color: #0F172A">Group Members</h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  style="background: rgba(0,82,255,0.06); color: #0052FF">
                  {{ $this->members->count() }} {{ $this->members->count() === 1 ? 'member' : 'members' }}
                </span>
              </div>

              @if($this->members->isEmpty())
                <div class="rounded-xl border py-16 flex flex-col items-center text-center" style="border-color: #E2E8F0">
                  <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4" style="background: #F1F5F9">
                    <x-heroicon-o-users class="h-7 w-7" style="color: #94A3B8" />
                  </div>
                  <p class="text-sm font-medium" style="color: #64748B">No members yet</p>
                </div>
              @else
                <div class="space-y-3">
                  @foreach($this->members as $member)
                    <div class="flex items-center gap-4 p-4 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                      style="border-color: #F1F5F9; background: #FAFAFA">
                      <div class="h-10 w-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
                        style="background: linear-gradient(135deg, #0052FF, #4D7CFF); color: white">
                        {{ strtoupper(substr($member->first_name, 0, 1)) }}
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                          <span class="font-semibold text-sm" style="color: #0F172A">
                            {{ $member->first_name }} {{ $member->last_name }}
                          </span>
                          @if($member->id === $this->group->leader_id)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                              style="background: rgba(0,82,255,0.1); color: #0052FF; border: 1px solid rgba(0,82,255,0.2)">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                              Leader
                            </span>
                          @endif
                        </div>
                        <p class="text-xs mt-0.5" style="color: #94A3B8">{{ $member->student_number }}</p>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          @endif

          {{-- ── Personnel ───────────────────────────────────────────────────────────── --}}
          @if($tab === 'personnel')
            <div class="p-5 sm:p-6">
              <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-base" style="color: #0F172A">Assigned Personnel</h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  style="background: rgba(0,82,255,0.06); color: #0052FF">
                  {{ count($this->personnels) }} assigned
                </span>
              </div>

              @if(count($this->personnels) === 0)
                <div class="rounded-xl border py-16 flex flex-col items-center text-center" style="border-color: #E2E8F0">
                  <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4" style="background: #F1F5F9">
                    <x-heroicon-o-user-group class="h-7 w-7" style="color: #94A3B8" />
                  </div>
                  <p class="text-sm font-medium" style="color: #64748B">No personnel assigned</p>
                </div>
              @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  @foreach($this->personnels as $person)
                    <div class="p-4 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                      style="border-color: #F1F5F9; background: #FAFAFA">
                      <div class="flex items-center gap-3 mb-3">
                        <div class="h-10 w-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
                          style="background: linear-gradient(135deg, #0052FF, #4D7CFF); color: white">
                          {{ strtoupper(substr($person['name'], 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                          <p class="font-semibold text-sm truncate" style="color: #0F172A">{{ $person['name'] }}</p>
                          <p class="text-xs truncate" style="color: #94A3B8">{{ $person['department'] }}</p>
                        </div>
                      </div>
                      <div class="pt-3 border-t" style="border-color: #E2E8F0">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                          style="background: rgba(0,82,255,0.06); color: #0052FF">
                          {{ $person['role'] }}
                        </span>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          @endif

          {{-- ── Consultations ───────────────────────────────────────────────────────── --}}
          @if($tab === 'consultation')
            <div class="p-5 sm:p-6">
              <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-base" style="color: #0F172A">My Consultations</h3>
                <button wire:click="mountAction('scheduleConsultation')"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                  style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.25)">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  Schedule
                </button>
              </div>

              @if(count($this->consultations) === 0)
                <div class="rounded-xl border py-16 flex flex-col items-center text-center" style="border-color: #E2E8F0">
                  <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4" style="background: #F1F5F9">
                    <svg class="h-7 w-7" style="color: #94A3B8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <p class="text-sm font-semibold mb-1" style="color: #0F172A">No consultations yet</p>
                  <p class="text-xs mb-5" style="color: #94A3B8">Schedule your first session with this group.</p>
                  <button wire:click="mountAction('scheduleConsultation')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5"
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.25)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Schedule Consultation
                  </button>
                </div>
              @else
                <div class="space-y-3">
                  @foreach($this->consultations as $consultation)
                    @php
                      $statusStyle = match($consultation['status']) {
                        'Completed' => 'background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2)',
                        'Scheduled' => 'background: rgba(0,82,255,0.06); color: #0052FF; border-color: rgba(0,82,255,0.18)',
                        'Cancelled' => 'background: rgba(239,68,68,0.06); color: #DC2626; border-color: rgba(239,68,68,0.18)',
                        default => 'background: #F1F5F9; color: #64748B; border-color: #E2E8F0',
                      };
                    @endphp
                    <div class="p-4 sm:p-5 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                      style="border-color: #F1F5F9; background: #FAFAFA">
                      <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-3 flex-wrap mb-2">
                            <h4 class="font-semibold text-sm" style="color: #0F172A">{{ $consultation['topic'] }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                              style="{{ $statusStyle }}">
                              {{ $consultation['status'] }}
                            </span>
                          </div>
                          <div class="flex flex-wrap items-center gap-4 text-xs" style="color: #94A3B8">
                            <span class="flex items-center gap-1.5">
                              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              {{ $consultation['date'] }}
                            </span>
                            <span class="flex items-center gap-1.5">
                              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                              {{ $consultation['time'] }}
                            </span>
                          </div>
                          @if($consultation['notes'])
                            <div class="mt-3 pt-3 border-t text-xs leading-relaxed" style="border-color: #E2E8F0; color: #64748B">
                              <span class="font-semibold" style="color: #475569">Notes: </span>{{ $consultation['notes'] }}
                            </div>
                          @endif
                        </div>

                        <x-filament::dropdown placement="bottom-end">
                          <x-slot name="trigger">
                            <button class="p-1.5 rounded-lg transition-colors duration-150 hover:bg-slate-100 shrink-0">
                              <svg class="h-4 w-4" style="color: #94A3B8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

          {{-- ── Proposal ─────────────────────────────────────────────────────────────── --}}
          @if($tab === 'proposal')
            <div class="p-5 sm:p-6">
              <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-base" style="color: #0F172A">Proposed Research Title</h3>
                <span class="text-xs italic" style="color: #94A3B8">View only</span>
              </div>

              @if(!$this->proposal)
                <div class="rounded-xl border py-16 flex flex-col items-center text-center" style="border-color: #E2E8F0">
                  <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4" style="background: #F1F5F9">
                    <svg class="h-7 w-7" style="color: #94A3B8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <p class="text-sm font-semibold mb-1" style="color: #0F172A">No proposal submitted</p>
                  <p class="text-xs" style="color: #94A3B8">This group hasn't submitted a research title yet.</p>
                </div>
              @else
                @php
                  $proposalStatusStyle = match($this->proposal->status) {
                    'approved' => 'background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2)',
                    'pending'  => 'background: rgba(245,158,11,0.08); color: #B45309; border-color: rgba(245,158,11,0.2)',
                    'rejected' => 'background: rgba(239,68,68,0.06); color: #DC2626; border-color: rgba(239,68,68,0.18)',
                    default    => 'background: #F1F5F9; color: #64748B; border-color: #E2E8F0',
                  };
                @endphp
                <div class="p-[2px] rounded-2xl" style="background: linear-gradient(135deg, #0052FF, #4D7CFF)">
                  <div class="bg-white rounded-[14px] p-6">
                    <div class="flex items-start justify-between gap-4 mb-3">
                      <h4 class="font-bold leading-snug flex-1"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: 1.15rem; color: #0F172A">
                        {{ $this->proposal->title }}
                      </h4>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border shrink-0"
                        style="{{ $proposalStatusStyle }}">
                        {{ ucfirst($this->proposal->status) }}
                      </span>
                    </div>
                    <p class="text-xs mb-5"
                      style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; letter-spacing: 0.04em">
                      Submitted {{ $this->proposal->created_at->format('M d, Y') }}
                    </p>

                    @if($this->proposal->description)
                      <div class="mb-5">
                        <p class="text-xs font-semibold mb-2 uppercase tracking-wider" style="color: #94A3B8">Description</p>
                        <p class="text-sm leading-relaxed" style="color: #475569">{{ $this->proposal->description }}</p>
                      </div>
                    @endif

                    @if($this->proposal->feedback)
                      <div class="pt-4 border-t" style="border-color: #EEF2FF">
                        <p class="text-xs font-semibold mb-2 uppercase tracking-wider" style="color: #94A3B8">Feedback</p>
                        <p class="text-sm leading-relaxed" style="color: #475569">{{ $this->proposal->feedback }}</p>
                      </div>
                    @endif
                  </div>
                </div>
              @endif
            </div>
          @endif

        </div>
      </div>

      {{-- ── Sidebar ──────────────────────────────────────────────────────────────────── --}}
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border overflow-hidden lg:sticky lg:top-6"
          style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
          <div class="h-[3px]" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

          <div class="p-5">
            <p class="font-bold mb-5 text-sm"
              style="font-family: 'JetBrains Mono', monospace; letter-spacing: 0.06em; text-transform: uppercase; color: #94A3B8">
              Group Overview
            </p>

            <div class="space-y-4">

              {{-- Leader --}}
              <div class="pb-4 border-b" style="border-color: #F1F5F9">
                <p class="text-xs mb-2 uppercase tracking-widest"
                  style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">Leader</p>
                @if($this->group->leader)
                  <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                      style="background: linear-gradient(135deg, #0052FF, #4D7CFF); color: white">
                      {{ strtoupper(substr($this->group->leader->first_name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold" style="color: #0F172A">
                      {{ $this->group->leader->first_name }} {{ $this->group->leader->last_name }}
                    </span>
                  </div>
                @else
                  <p class="text-sm italic" style="color: #94A3B8">No leader assigned</p>
                @endif
              </div>

              {{-- Members count --}}
              <div class="pb-4 border-b" style="border-color: #F1F5F9">
                <p class="text-xs mb-1.5 uppercase tracking-widest"
                  style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">Members</p>
                <p class="font-bold" style="font-size: 2rem; color: #0052FF; line-height: 1">
                  {{ $this->members->count() }}
                </p>
              </div>

              {{-- Course --}}
              <div class="pb-4 border-b" style="border-color: #F1F5F9">
                <p class="text-xs mb-1.5 uppercase tracking-widest"
                  style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">Course</p>
                <p class="text-sm font-semibold" style="color: #0F172A">{{ $this->group->section->program->name }}</p>
              </div>

              {{-- Section --}}
              <div>
                <p class="text-xs mb-1.5 uppercase tracking-widest"
                  style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">Section</p>
                <p class="text-sm font-semibold" style="color: #0F172A">{{ $this->group->section->name }}</p>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <x-filament-actions::modals />
