<?php

use App\Models\Section;
use App\Models\Student;
use App\Services\GroupService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Section $section;

    #[Computed]
    public function students()
    {
        return $this->section->students()->with([
            'groups' => function ($query) {
                $query->where('section_id', $this->section->id);
            },
        ])->get()->map(function ($student) {
            $group = $student->groups->first();
            $isLeader = $group && $group->leader_id === $student->id;

            return [
                'id' => $student->id,
                'name' => $student->first_name.' '.$student->last_name,
                'student_number' => $student->student_number,
                'group' => $group?->name ?? 'No Group',
                'role' => $isLeader ? 'Leader' : 'Member',
                'has_group' => $group !== null,
            ];
        })->toArray();
    }

    public function addStudentsAction(): Action
    {
        return Action::make('addStudents')
            ->modalWidth('2xl')
            ->modalCloseButton(false)
            ->label('Add Students')
            ->icon(Heroicon::UserPlus)
            ->modalHeading('Add Students to Section')
            ->modalDescription(fn () => "Select students from {$this->section->program->name} to add to this section.")
            ->form(function () {
                $availableStudents = Student::whereDoesntHave('sections', function ($query) {
                    $query->where('section_id', $this->section->id);
                })
                    ->where('program_id', $this->section->program_id)
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
                        ->columns(3),
                ];
            })
            ->successNotificationTitle('Students added successfully')
            ->action(function (array $data): void {
                if (! empty($data['students'])) {
                    $this->section->students()->attach($data['students']);
                    unset($this->students);
                }
            });
    }

    public function removeStudentAction(): Action
    {
        return Action::make('removeStudent')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->modalHeading('Remove Student from Section')
            ->modalDescription(fn (array $arguments) => 'Are you sure you want to remove this student from the section? They will be unassigned from any groups.')
            ->modalSubmitActionLabel('Yes, Remove')
            ->color('danger')
            ->icon(Heroicon::Trash)
            ->successNotificationTitle('Student removed from section')
            ->action(function (array $arguments): void {
                $studentId = $arguments['studentId'];

                // Remove student from any groups in this section
                $groupService = app(GroupService::class);
                $groupService->removeStudentFromSectionGroups($studentId, $this->section->id);

                // Remove student from section
                $this->section->students()->detach($studentId);
                unset($this->students);
            });
    }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-4">
  <div class="mb-4 flex items-center justify-between">
    <div class="relative flex-1 max-w-md">
      <input type="text" placeholder="Search students..."
        class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-md text-sm">
      <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>
    <div class="flex gap-2">
      <button
        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50">
        Export List
      </button>
      <x-filament::button wire:click="mountAction('addStudents')" class="bg-blue-500 text-white">
        Add Student
      </x-filament::button>
    </div>
  </div>

  @if(count($this->students) === 0)
    <div class="text-center py-12">
      <x-heroicon-o-academic-cap class="h-16 w-16 mx-auto text-slate-300 mb-4" />
      <p class="text-slate-500 text-base font-medium mb-1">No students enrolled yet</p>
      <p class="text-slate-400 text-sm">Add students to this section to get started</p>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($this->students as $student)
        <div class="bg-white border border-slate-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition-all">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="font-semibold text-slate-900">{{ $student['name'] }}</h3>
              <p class="text-xs text-slate-500 mt-0.5">{{ $student['student_number'] }}</p>
            </div>
            <div class="flex items-center gap-2">
              <span
                class="px-2 py-0.5 text-xs font-medium rounded {{ $student['role'] === 'Leader' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $student['role'] }}
              </span>

              <x-filament::dropdown placement="bottom-end">
                <x-slot name="trigger">
                  <button class="p-1 hover:bg-slate-100 rounded transition-colors">
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </button>
                </x-slot>

                <x-filament::dropdown.list>
                  {{-- <x-filament::dropdown.list.item icon="heroicon-o-eye" wire:click="viewStudent({{ $student['id'] }})">
                    View Details
                  </x-filament::dropdown.list.item>

                  <x-filament::dropdown.list.item icon="heroicon-o-arrows-right-left"
                    wire:click="changeGroup({{ $student['id'] }})">
                    Change Group
                  </x-filament::dropdown.list.item> --}}

                  <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                    wire:click="mountAction('removeStudent', { studentId: {{ $student['id'] }} })">
                    Remove from Section
                  </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
              </x-filament::dropdown>
            </div>
          </div>

          <div class="flex items-center gap-2 text-sm text-slate-600">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>{{ $student['group'] }}</span>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <x-filament-actions::modals />
</div>
