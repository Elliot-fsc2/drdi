<?php

use App\Models\Group;
use App\Models\Section;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas {
  use InteractsWithActions;
  use InteractsWithSchemas;

  public Group $group;
  public Section $section;

  #[Url]
  public $tab = 'members';

  public bool $selectingLeader = false;

  #[Computed]
  public function routePrefix(): string
  {
    $user = auth()->user();
    $isRDO = $user->profileable_type === \App\Models\Instructor::class
      && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

    return $isRDO ? 'rdo' : 'instructor';
  }

  public function mount()
  {
    abort_if($this->group->section->instructor_id !== auth()->user()->profileable->id, 403);
    abort_if($this->group->section_id !== $this->section->id, 403);
  }

  #[Computed]
  public function members()
  {
    return $this->group->members()
      ->select('students.id', 'students.first_name', 'students.last_name', 'students.student_number')
      ->orderByRaw('students.id = ? DESC', [$this->group->leader_id])
      ->get();
  }

  public function addMembersAction(): Action
  {
    return Action::make('addMembers')
      ->modalWidth('2xl')
      ->modalCloseButton(false)
      ->label('Add Member')
      ->icon(Heroicon::UserPlus)
      ->modalHeading('Add Members to Group')
      ->modalDescription(fn() => "Select students from {$this->section->name} to add to this group.")
      ->form(function () {
        $availableStudents = $this->section->students()
          ->whereDoesntHave('groups', function ($query) {
            $query->where('section_id', $this->section->id);
          })
          ->orderBy('last_name')
          ->get()
          ->mapWithKeys(function ($student) {
            return [$student->id => "{$student->last_name} {$student->first_name} ({$student->student_number})"];
          })
          ->toArray();

        if (empty($availableStudents)) {
          return [];
        }

        return [
          CheckboxList::make('students')
            ->label('Select Students')
            ->options($availableStudents)
            ->required()
            ->searchable()
            ->bulkToggleable()
            ->columns(3)
        ];
      })
      ->successNotificationTitle('Members added successfully')
      ->action(function (array $data): void {
        if (!empty($data['students'])) {
          $this->group->members()->attach($data['students']);
          unset($this->members);
        }
      });
  }

  public function removeMemberAction(): Action
  {
    return Action::make('removeMember')
      ->before(function (Action $action, array $arguments): void {
        $studentId = $arguments['studentId'];

        if ($studentId == $this->group->leader_id) {
          \Filament\Notifications\Notification::make()
            ->title('Cannot remove leader')
            ->body('Please assign a new leader before removing this member.')
            ->danger()
            ->send();

          $action->cancel();
        }
      })
      ->requiresConfirmation()
      ->modalCloseButton(false)
      ->modalHeading('Remove Member from Group')
      ->modalDescription(fn(array $arguments) => "Are you sure you want to remove this student from the group?")
      ->modalSubmitActionLabel('Yes, Remove')
      ->color('danger')
      ->icon(Heroicon::Trash)
      ->successNotificationTitle('Member removed from group')
      ->action(function (array $arguments): void {
        $studentId = $arguments['studentId'];
        $this->group->members()->detach($studentId);
        unset($this->members);
      });
  }

  public function toggleSelectLeader()
  {
    $this->selectingLeader = !$this->selectingLeader;
  }

  public function selectLeader(int $studentId): void
  {
    $this->group->update(['leader_id' => $studentId]);
    $this->selectingLeader = false;
    unset($this->members);

    \Filament\Notifications\Notification::make()
      ->title('Leader updated successfully')
      ->success()
      ->send();
  }

  #[Computed]
  public function leader()
  {
    return $this->group->leader;
  }

  #[Computed]
  public function membersCount()
  {
    return $this->group->members()->count();
  }

  public $proposedTitle = [
    'title' => 'AI-Based Student Performance Prediction System',
    'description' => 'A machine learning system that predicts student performance based on various academic and behavioral factors.',
    'status' => 'Under Review',
    'submitted_date' => '2026-02-10',
  ];
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">
  {{ $this->group->name }} - {{ $this->section->name }}
</x-slot>

<div class="p-0 lg:p-4 bg-slate-50 min-h-screen">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-4 md:mb-6 px-3 lg:px-0">
      <div class="flex items-center gap-2 text-xs md:text-sm text-slate-600 mb-3 pt-3 lg:pt-0">
        <a href="{{ route($this->routePrefix . '.classes') }}" wire:navigate class="hover:text-blue-600">My Classes</a>
        <span>/</span>
        <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $this->section->id]) }}" wire:navigate
          class="hover:text-blue-600">{{ $this->section->name }}</a>
        <span>/</span>
        <span class="text-slate-900 font-medium">{{ $this->group->name }}</span>
      </div>

      <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-2xl md:text-3xl font-bold text-slate-900">{{ $this->group->name }}</h1>
          <p class="text-slate-600 mt-1 text-sm md:text-base">{{ $this->section->name }} •
            {{ $this->section->program->name }}
          </p>
        </div>

        <!-- Desktop Buttons -->
        <div class="hidden sm:flex gap-2">
          <button
            class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50">
            Export Report
          </button>
          <button class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
            Group Settings
          </button>
        </div>

        <!-- Mobile Dropdown -->
        <div class="sm:hidden">
          <x-filament::dropdown placement="bottom-end">
            <x-slot name="trigger">
              <button class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </x-slot>

            <x-filament::dropdown.list>
              <x-filament::dropdown.list.item icon="heroicon-o-arrow-down-tray">
                Export Report
              </x-filament::dropdown.list.item>
              <x-filament::dropdown.list.item icon="heroicon-o-cog-6-tooth">
                Group Settings
              </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
          </x-filament::dropdown>
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
              <a href="?tab=title" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'title' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Proposed Title
              </a>
              <a href="?tab=personnel" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'personnel' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Personnel
              </a>
              <a href="?tab=consultation" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'consultation' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Consultation
              </a>
              <a href="?tab=fees" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap {{ $tab === 'fees' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Fees
              </a>
            </div>
          </div>

          <!-- Members Tab -->
          @if($tab === 'members')
            <div class="p-3 md:p-4">
              <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="font-semibold text-slate-900">Group Members</h3>
                <div class="flex flex-col sm:flex-row gap-2">
                  @if($selectingLeader)
                    <button wire:click="toggleSelectLeader"
                      class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-md hover:bg-slate-700 w-full sm:w-auto">
                      Cancel
                    </button>
                  @else
                    <button wire:click="toggleSelectLeader"
                      class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50 w-full sm:w-auto">
                      Select New Leader
                    </button>
                    <x-filament::button wire:click="mountAction('addMembers')"
                      class="bg-blue-500 text-white w-full sm:w-auto">
                      Add Member
                    </x-filament::button>
                  @endif
                </div>
              </div>

              @if($selectingLeader)
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <p class="text-sm text-blue-800">
                    <strong>Select New Leader:</strong> Click on any member below to assign them as the new group leader.
                  </p>
                </div>
              @endif

              <div class="space-y-3">
                @foreach($this->members as $member)
                  <div
                    class="border border-slate-200 rounded-lg p-3 md:p-4 transition-all bg-white
                                      {{ $selectingLeader ? 'hover:border-blue-500 hover:shadow-md cursor-pointer' : 'hover:border-blue-300' }}"
                    @if($selectingLeader) wire:click="selectLeader({{ $member->id }})" @endif>
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-3 md:gap-4 min-w-0">
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

                      @if(!$selectingLeader)
                        <x-filament::dropdown placement="bottom-end">
                          <x-slot name="trigger">
                            <button class="p-1 hover:bg-slate-100 rounded transition-colors shrink-0">
                              <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                              </svg>
                            </button>
                          </x-slot>

                          <x-filament::dropdown.list>
                            <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                              wire:click="mountAction('removeMember', { studentId: {{ $member->id }} })">
                              Remove from Group
                            </x-filament::dropdown.list.item>
                          </x-filament::dropdown.list>
                        </x-filament::dropdown>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <!-- Proposed Title Tab -->
          @if($tab === 'title')
            <livewire:instructor::my-classes.group.proposals :section="$this->section" :group="$this->group" />
          @endif

          <!-- Personnel Tab -->
          @if($tab === 'personnel')
            <livewire:instructor::my-classes.group.personnels :section="$this->section" :group="$this->group" />
          @endif

          <!-- Consultation Tab -->
          @if($tab === 'consultation')
            <livewire:instructor::my-classes.group.consultations :section="$this->section" :group="$this->group" />
          @endif

          <!-- Fees Tab -->
          @if($tab === 'fees')
            <livewire:instructor::my-classes.group.fees :section="$this->section" :group="$this->group" />
          @endif
        </div>
      </div>

      <!-- Stats Sidebar -->
      <div class="lg:col-span-1">
        <div
          class="bg-white border-x-0 border-y border-slate-200 lg:border lg:rounded-lg p-4 md:p-5 lg:sticky lg:top-4">
          <h3 class="font-bold text-slate-900 mb-4">Group Overview</h3>

          <div class="space-y-4">
            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Leader</div>
              @if($this->leader)
                <div class="text-base font-semibold text-slate-900">
                  {{ $this->leader->first_name }} {{ $this->leader->last_name }}
                </div>
              @else
                <div class="text-base text-slate-500 italic">No leader assigned</div>
              @endif
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Members</div>
              <div class="text-2xl md:text-3xl font-bold text-slate-900">{{ $this->membersCount }}</div>
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Course</div>
              <div class="text-base font-semibold text-slate-900">{{ $this->section->program->name }}</div>
            </div>

            <div>
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Section</div>
              <div class="text-base font-semibold text-slate-900">{{ $this->section->name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <x-filament-actions::modals />
  {{-- <div class="h-4 lg:hidden"></div> --}}
</div>