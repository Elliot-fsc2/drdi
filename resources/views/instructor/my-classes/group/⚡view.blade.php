<?php

use App\Models\Group;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\CheckboxList;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Services\GroupService;

new class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Group $group;
    public Section $section;
    private GroupService $groupService;

    #[Url]
    public $tab = 'members';

    public bool $selectingLeader = false;

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    public function boot(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function mount()
    {
        abort_if($this->group->section->instructor_id !== auth()->user()->profileable->id, 403);
        abort_if($this->group->section_id !== $this->section->id, 403);
    }

    #[Computed]
    public function members()
    {
        return $this->group
            ->members()
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
                $availableStudents = $this->section
                    ->students()
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

                return [CheckboxList::make('students')->label('Select Students')->options($availableStudents)->required()->searchable()->bulkToggleable()->columns(3)];
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
                    \Filament\Notifications\Notification::make()->title('Cannot remove leader')->body('Please assign a new leader before removing this member.')->danger()->send();

                    $action->cancel();
                }
            })
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->modalHeading('Remove Member from Group')
            ->modalDescription(fn(array $arguments) => 'Are you sure you want to remove this student from the group?')
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

        \Filament\Notifications\Notification::make()->title('Leader updated successfully')->success()->send();
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

    public function deleteGroupAction(): Action
    {
        return Action::make('deleteGroup')
            ->modalCloseButton(false)
            ->requiresConfirmation()
            ->databaseTransaction()
            ->modalHeading('Delete Group')
            ->modalDescription('Are you sure you want to delete this group? This action cannot be undone.')
            ->modalSubmitActionLabel('Yes, Delete')
            ->color('danger')
            ->icon(Heroicon::Trash)
            ->successNotificationTitle('Group deleted successfully')
            ->action(function (): void {
                $this->groupService->delete($this->group);
                $this->redirectRoute($this->routePrefix . '.classes.view', ['section' => $this->section->id], navigate: true);
            });
    }
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">{{ $this->group->name }} - {{ $this->section->name }}</x-slot>

<div class="min-h-screen relative" style="background: #F8FAFC">

    {{-- Ambient glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">

        {{-- ── Page Header ──────────────────────────────────────────────────────────────── --}}
        <div class="mb-8 sm:mb-10">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 mb-5 text-sm" style="color: #94A3B8">
                <a href="{{ route($this->routePrefix . '.classes') }}" wire:navigate
                    class="transition-colors duration-150 hover:text-blue-500 font-medium">My Classes</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $this->section->id]) }}"
                    wire:navigate
                    class="transition-colors duration-150 hover:text-blue-500 font-medium">{{ $this->section->name }}</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <span style="color: #0F172A; font-weight: 600">{{ $this->group->name }}</span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    {{-- Section label badge --}}
                    <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-4"
                        style="border-color: rgba(0,82,255,0.25); background: rgba(0,82,255,0.05)">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                            My Classes
                        </span>
                    </div>

                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        {{ $this->group->name }}
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        {{ $this->section->name }} &bull; {{ $this->section->program->name }}
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    {{-- Tab switcher --}}
                    <div class="inline-flex items-center gap-1 rounded-xl p-1 flex-wrap"
                        style="background: #EEF2FF; border: 1px solid rgba(0,82,255,0.12)">
                        @foreach ([
        'members' => 'Members',
        'title' => 'Proposed Title',
        'personnel' => 'Personnel',
        'consultation' => 'Consultation',
        'fees' => 'Fees',
    ] as $key => $label)
                            <a href="?tab={{ $key }}" wire:navigate
                                class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap"
                                style="{{ $tab === $key ? 'background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.3)' : 'color: #64748B' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Group Settings --}}
                    <x-filament::dropdown placement="bottom-end">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                style="background: white; border: 1px solid #E2E8F0; color: #475569; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                                <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                                Settings
                            </button>
                        </x-slot>
                        <x-filament::dropdown.list>
                            <x-filament::dropdown.list.item tag="a" wire:navigate
                                href="{{ route('repository-requirement', ['group' => $this->group->id]) }}"
                                icon="heroicon-o-check" color="success">
                                Turn Over to Library
                            </x-filament::dropdown.list.item>
                            <x-filament::dropdown.list.item wire:click="mountAction('deleteGroupAction')"
                                icon="heroicon-o-trash" color="danger">
                                Delete Group
                            </x-filament::dropdown.list.item>
                        </x-filament::dropdown.list>
                    </x-filament::dropdown>
                </div>
            </div>
        </div>

        {{-- ── Two-column layout ────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 lg:gap-6">

            {{-- ── Main panel ──────────────────────────────────────────────────────────────── --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                    {{-- Gradient top stripe --}}
                    <div class="h-[3px]" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

                    {{-- ── Members Tab ─────────────────────────────────────────────────────────── --}}
                    @if ($tab === 'members')
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
                                <h3 class="font-bold text-base" style="color: #0F172A">Group Members</h3>
                                <div class="flex gap-2">
                                    @if ($selectingLeader)
                                        <button wire:click="toggleSelectLeader"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5"
                                            style="background: white; border: 1px solid #E2E8F0; color: #64748B; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                            Cancel
                                        </button>
                                    @else
                                        <button wire:click="toggleSelectLeader"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5"
                                            style="background: white; border: 1px solid #E2E8F0; color: #64748B; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                                            <x-heroicon-o-star class="h-4 w-4" />
                                            Select Leader
                                        </button>
                                        <button wire:click="mountAction('addMembers')"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.25)">
                                            <x-heroicon-o-user-plus class="h-4 w-4" />
                                            Add Member
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if ($selectingLeader)
                                <div class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3"
                                    style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.04)">
                                    <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0"
                                        style="color: #0052FF" />
                                    <p class="text-sm" style="color: #1E40AF">
                                        <strong>Select New Leader:</strong> Click on any member below to assign them as
                                        the group leader.
                                    </p>
                                </div>
                            @endif

                            @if ($this->members->isEmpty())
                                <div class="rounded-xl border py-16 flex flex-col items-center text-center"
                                    style="border-color: #E2E8F0">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                                        style="background: #F1F5F9">
                                        <x-heroicon-o-users class="h-7 w-7" style="color: #94A3B8" />
                                    </div>
                                    <p class="text-sm font-medium" style="color: #64748B">No members yet</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($this->members as $member)
                                        <div class="flex items-center gap-4 p-4 rounded-xl border transition-all duration-200
                      {{ $selectingLeader ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md' : 'hover:-translate-y-0.5 hover:shadow-md' }}"
                                            style="border-color: #F1F5F9; background: #FAFAFA"
                                            @if ($selectingLeader) wire:click="selectLeader({{ $member->id }})" @endif>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-semibold text-sm" style="color: #0F172A">
                                                        {{ $member->first_name }} {{ $member->last_name }}
                                                    </span>
                                                    @if ($member->id === $this->group->leader_id)
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                                            style="background: rgba(124,58,237,0.08); color: #7C3AED; border: 1px solid rgba(124,58,237,0.2)">
                                                            <x-heroicon-s-star class="h-2.5 w-2.5" />
                                                            Leader
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-xs mt-0.5" style="color: #94A3B8">
                                                    {{ $member->student_number }}</p>
                                            </div>

                                            @if (!$selectingLeader)
                                                <x-filament::dropdown placement="bottom-end">
                                                    <x-slot name="trigger">
                                                        <button
                                                            class="p-1.5 rounded-lg transition-colors duration-150 hover:bg-slate-100 shrink-0">
                                                            <x-heroicon-o-ellipsis-vertical class="h-4 w-4"
                                                                style="color: #94A3B8" />
                                                        </button>
                                                    </x-slot>
                                                    <x-filament::dropdown.list>
                                                        <x-filament::dropdown.list.item icon="heroicon-o-trash"
                                                            color="danger"
                                                            wire:click="mountAction('removeMember', { studentId: {{ $member->id }} })">
                                                            Remove from Group
                                                        </x-filament::dropdown.list.item>
                                                    </x-filament::dropdown.list>
                                                </x-filament::dropdown>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ── Proposed Title Tab ──────────────────────────────────────────────────── --}}
                    @if ($tab === 'title')
                        <livewire:instructor::my-classes.group.proposals :section="$this->section" :group="$this->group" />
                    @endif

                    {{-- ── Personnel Tab ───────────────────────────────────────────────────────── --}}
                    @if ($tab === 'personnel')
                        <livewire:instructor::my-classes.group.personnels :section="$this->section" :group="$this->group" />
                    @endif

                    {{-- ── Consultation Tab ────────────────────────────────────────────────────── --}}
                    @if ($tab === 'consultation')
                        <livewire:instructor::my-classes.group.consultations :section="$this->section" :group="$this->group" />
                    @endif

                    {{-- ── Fees Tab ────────────────────────────────────────────────────────────── --}}
                    @if ($tab === 'fees')
                        <livewire:instructor::my-classes.group.fees :section="$this->section" :group="$this->group" />
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
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Leader</p>
                                @if ($this->leader)
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-sm font-semibold" style="color: #0F172A">
                                            {{ $this->leader->first_name }} {{ $this->leader->last_name }}
                                        </span>
                                    </div>
                                @else
                                    <p class="text-sm italic" style="color: #94A3B8">No leader assigned</p>
                                @endif
                            </div>

                            {{-- Members count --}}
                            <div class="pb-4 border-b" style="border-color: #F1F5F9">
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Members</p>
                                <p class="font-bold" style="font-size: 2rem; color: #0052FF; line-height: 1">
                                    {{ $this->membersCount }}
                                </p>
                            </div>

                            {{-- Course --}}
                            <div class="pb-4 border-b" style="border-color: #F1F5F9">
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Course</p>
                                <p class="text-sm font-semibold" style="color: #0F172A">
                                    {{ $this->section->program->name }}</p>
                            </div>

                            {{-- Section --}}
                            <div>
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Section</p>
                                <p class="text-sm font-semibold" style="color: #0F172A">{{ $this->section->name }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <x-filament-actions::modals />
</div>
