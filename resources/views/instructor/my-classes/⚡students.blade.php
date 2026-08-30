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
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::instructor.app')] class extends Component implements HasActions, HasSchemas {
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
                'user',
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
                    'avatar_url' => $student->user?->avatar_url ?? asset('images/default-avatar.png'),
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
            ->schema(function () {
                $availableStudents = Student::whereDoesntHave('sections', function ($query) {
                    $query->whereHas('semester', function ($q) {
                        $q->active();
                    });
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

<div class="space-y-4 p-4 md:p-5">
    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 shadow-sm backdrop-blur sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-500 text-sm font-semibold text-white shadow-sm">
                {{ count($this->students) }}
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">Students</p>
                <p class="text-sm text-slate-500">Roster for this section</p>
            </div>
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
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <ul role="list" class="divide-y divide-slate-200">
                @foreach ($this->students as $student)
                    <li class="group flex flex-col gap-4 px-4 py-4 transition-colors hover:bg-slate-50/80 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-500 text-sm font-semibold text-white shadow-sm">
                                @if ($student['avatar_url'])
                                    <img src="{{ $student['avatar_url'] }}" alt="{{ $student['name'] }}"
                                        class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(mb_substr($student['name'], 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $student['name'] }}</p>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $student['role'] === 'Leader' ? 'border border-blue-200 bg-blue-50 text-blue-700' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                                        {{ $student['role'] }}
                                    </span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                    <span>{{ $student['student_number'] }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                    <span class="{{ $student['has_group'] ? 'text-slate-600' : 'text-slate-400' }}">
                                        {{ $student['group'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-start sm:self-center">
                            <x-filament::dropdown placement="bottom-end">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
