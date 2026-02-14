<?php

use App\Models\Group;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasSchemas {
  use InteractsWithSchemas;

  public Section $section;

  public ?array $data = [];

  public array $selectedMembers = [];

  public function mount(): void
  {
    $this->form->fill();
  }

  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        TextInput::make('name')
          ->label('Group Name')
          ->placeholder('e.g., Group 1')
          ->required()
          ->maxLength(255)
          ->live(),

        Select::make('leader_id')
          ->label('Group Leader')
          ->placeholder('Select a leader')
          ->options($this->availableStudents())
          ->required()
          ->searchable()
          ->preload()
          ->live()
          ->afterStateUpdated(function ($state) {
            // Remove leader from selected members if present
            $this->selectedMembers = array_diff($this->selectedMembers, [$state]);
          }),
      ])
      ->statePath('data');
  }

  #[Computed]
  public function availableStudents()
  {
    return $this->section->students()
      ->whereDoesntHave('groups', function ($query) {
        $query->where('section_id', $this->section->id);
      })
      ->get()
      ->mapWithKeys(function ($student) {
        return [$student->id => $student->first_name . ' ' . $student->last_name . ' (' . $student->student_number . ')'];
      });
  }

  #[Computed]
  public function availableMembers()
  {
    $leaderId = $this->data['leader_id'] ?? null;

    return $this->section->students()
      ->whereDoesntHave('groups', function ($query) {
        $query->where('section_id', $this->section->id);
      })
      ->when($leaderId, fn($query) => $query->where('students.id', '!=', $leaderId))
      ->get()
      ->mapWithKeys(function ($student) {
        return [
          $student->id => [
            'name' => $student->first_name . ' ' . $student->last_name,
            'student_number' => $student->student_number,
          ],
        ];
      });
  }

  public function create(): void
  {
    $data = $this->form->getState();

    if (empty($data['name']) || empty($data['leader_id'])) {
      Notification::make()
        ->title('Validation Error')
        ->body('Please fill in all required fields.')
        ->danger()
        ->send();

      return;
    }

    $group = Group::create([
      'name' => $data['name'],
      'section_id' => $this->section->id,
      'leader_id' => $data['leader_id'],
    ]);

    // Add leader and selected members
    $members = $this->selectedMembers;
    $members[] = $data['leader_id'];
    $members = array_unique($members);

    $group->members()->attach($members);

    Notification::make()
      ->title('Group created successfully')
      ->body('The research group has been created with ' . count($members) . ' member(s).')
      ->success()
      ->send();

    $this->redirect(route('instructor.classes.view', ['section' => $this->section->id, 'tab' => 'groups']), navigate: true);
  }
};
?>

<x-slot name="title">
  Create Group - {{ $section->name }}
</x-slot>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-3 sm:p-4 lg:p-6 bg-slate-50 min-h-screen">
  <div class="max-w-7xl mx-auto">

    <!-- Header with Breadcrumb -->
    <div class="mb-6">
      <div class="flex items-center gap-2 text-sm text-slate-600 mb-4">
        <a href="{{ route('instructor.classes') }}" wire:navigate class="hover:text-blue-600 transition-colors">My
          Classes</a>
        <x-heroicon-o-chevron-right class="h-4 w-4" />
        <a href="{{ route('instructor.classes.view', ['section' => $section->id]) }}" wire:navigate
          class="hover:text-blue-600 transition-colors">{{ $section->name }}</a>
        <x-heroicon-o-chevron-right class="h-4 w-4" />
        <span class="text-slate-900 font-medium">Create Group</span>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Create Research Group</h1>
          <p class="text-slate-600 mt-1 text-sm sm:text-base">{{ $section->name }} • {{ $section->semester->name }}</p>
        </div>

        <div class="flex gap-2">
          <a href="{{ route('instructor.classes.view', ['section' => $section->id, 'tab' => 'groups']) }}" wire:navigate
            class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-2">
              <x-heroicon-o-x-mark class="h-4 w-4" />
              <span>Cancel</span>
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left Side: Form -->
      <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-lg p-6 sticky top-6">
          <h2 class="text-lg font-bold text-slate-900 mb-1">Group Information</h2>
          <p class="text-sm text-slate-600 mb-6">Enter the basic details for the research group.</p>

          <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-6 pt-6 border-t border-slate-200">
              <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-600">Selected Members:</span>
                  <span class="font-semibold text-slate-900"
                    x-text="$wire.selectedMembers.length + ({{ isset($data['leader_id']) ? 1 : 0 }})"></span>
                </div>

                @if(isset($data['leader_id']))
                  <div class="text-xs text-slate-500">
                    <x-heroicon-o-information-circle class="h-4 w-4 inline mr-1" />
                    The group leader will be automatically included as a member.
                  </div>
                @endif
              </div>
            </div>

            <button type="submit"
              class="w-full mt-6 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              @disabled(empty($data['name']) || empty($data['leader_id']))>
              <div class="flex items-center justify-center gap-2">
                <x-heroicon-o-user-group class="h-5 w-5" />
                <span>Create Group</span>
              </div>
            </button>
          </form>
        </div>
      </div>

      <!-- Right Side: Members Selection -->
      <div class="lg:col-span-2">
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
          <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Select Members</h2>
            <p class="text-sm text-slate-600">
              Choose students from {{ $section->name }} to add to this group.
              @if(isset($data['leader_id']))
                The group leader is automatically included.
              @endif
            </p>
          </div>

          <div class="p-6" x-data="{
            toggleMember(id) {
              const index = $wire.selectedMembers.indexOf(id);
              if (index > -1) {
                $wire.selectedMembers.splice(index, 1);
              } else {
                $wire.selectedMembers.push(id);
              }
            },
            isSelected(id) {
              return $wire.selectedMembers.includes(id);
            },
            selectAll() {
              $wire.selectedMembers = {{ json_encode(array_keys($this->availableMembers->toArray())) }};
            },
            deselectAll() {
              $wire.selectedMembers = [];
            }
          }">
            @if(count($this->availableMembers) === 0)
              <div class="text-center py-12">
                <x-heroicon-o-users class="h-16 w-16 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-500 text-base font-medium mb-1">
                  @if(isset($data['leader_id']))
                    No other students available
                  @else
                    Select a group leader first
                  @endif
                </p>
                <p class="text-slate-400 text-sm">
                  @if(isset($data['leader_id']))
                    All students in this section are already assigned.
                  @else
                    Choose a leader from the form to see available members.
                  @endif
                </p>
              </div>
            @else
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($this->availableMembers as $id => $student)
                  <div wire:key="student-{{ $id }}" @click="toggleMember({{ $id }})"
                    :class="isSelected({{ $id }}) ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-200' : 'bg-white'"
                    class="border border-slate-200 rounded-lg p-4 cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50/50">
                    <div class="flex items-start justify-between">
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                          <div :class="isSelected({{ $id }}) ? 'bg-blue-600 border-blue-600' : 'border-slate-300 bg-white'"
                            class="shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center">
                            <x-heroicon-o-check x-show="isSelected({{ $id }})" class="h-3 w-3 text-white stroke-2" />
                          </div>
                          <h3 class="font-semibold text-slate-900 text-sm truncate">{{ $student['name'] }}</h3>
                        </div>
                        <p class="text-xs text-slate-600 mt-1 ml-7">{{ $student['student_number'] }}</p>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              <!-- Select All / Deselect All -->
              <div class="mt-4 flex gap-2">
                <button type="button" @click="selectAll()"
                  class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors">
                  Select All ({{ count($this->availableMembers) }})
                </button>
                <button type="button" @click="deselectAll()"
                  class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 border border-slate-200 rounded-md hover:bg-slate-200 transition-colors">
                  Deselect All
                </button>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>