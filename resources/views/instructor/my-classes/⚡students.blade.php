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

new class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Section $section;

    #[Computed]
    public function students()
    {
        return $this->section
            ->students()
            ->with([
                'groups' => function ($query) {
                    $query->where('section_id', $this->section->id);
                },
            ])
            ->get()
            ->map(function ($student) {
                $group = $student->groups->first();
                $isLeader = $group && $group->leader_id === $student->id;

                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_number' => $student->student_number,
                    'group' => $group?->name ?? 'No Group',
                    'role' => $isLeader ? 'Leader' : 'Member',
                    'has_group' => $group !== null,
                ];
            })
            ->toArray();
    }

    public function addStudentsAction(): Action
    {
        return Action::make('addStudents')
            ->modalWidth('2xl')
            ->modalCloseButton(false)
            ->label('Add Students')
            ->icon(Heroicon::UserPlus)
            ->modalHeading('Add Students to Section')
            ->modalDescription(fn() => "Select students from {$this->section->program->name} to add to this section.")
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

                return [CheckboxList::make('students')->label('Select Students')->options($availableStudents)->required()->searchable()->bulkToggleable()->columns(3)];
            })
            ->successNotificationTitle('Students added successfully')
            ->action(function (array $data): void {
                if (!empty($data['students'])) {
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
            ->modalDescription(fn(array $arguments) => 'Are you sure you want to remove this student from the section? They will be unassigned from any groups.')
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

<div class="space-y-4 p-4 md:p-5">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            @if (count($this->students) > 0)
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ count($this->students) }} students
                </span>
            @endif
        </div>
        <x-filament::button wire:click="mountAction('addStudents')" color="info">
            Add Student
        </x-filament::button>
    </div>

    @if (count($this->students) === 0)
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-10 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
            </div>
            <p class="mb-1 font-semibold text-slate-700">No students enrolled yet</p>
            <p class="text-sm text-slate-400">Add students to this section to get started.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-2.5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->students as $student)
                <div
                    class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white transition-all duration-200 hover:border-blue-200 hover:shadow-md">
                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl"
                        style="background: linear-gradient(to bottom, #0052FF, #4D7CFF);"></div>
                    <div class="py-4 pl-5 pr-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold text-blue-700"
                                    style="background: linear-gradient(135deg, rgba(0,82,255,0.12), rgba(77,124,255,0.08));">
                                    {{ substr($student['name'], 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $student['name'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $student['student_number'] }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $student['role'] === 'Leader' ? 'border border-purple-200 bg-purple-50 text-purple-700' : 'border border-slate-200 bg-slate-50 text-slate-500' }}">
                                    {{ $student['role'] }}
                                </span>
                                <x-filament::dropdown placement="bottom-end">
                                    <x-slot name="trigger">
                                        <button class="rounded-lg p-1 transition-colors hover:bg-slate-100">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                    </x-slot>
                                    <x-filament::dropdown.list>
                                        <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                                            wire:click="mountAction('removeStudent', { studentId: {{ $student['id'] }} })">
                                            Remove from Section
                                        </x-filament::dropdown.list.item>
                                    </x-filament::dropdown.list>
                                </x-filament::dropdown>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-400">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>{{ $student['group'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</div>
