<?php

use App\Models\Group;
use App\Models\Section;
use App\Services\FeeService;
use App\Services\GroupService;
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

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Group Name')->placeholder('e.g., Group 1')->required()->maxLength(255)->live(),

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
                        $this->selectedMembers = array_values(array_diff($this->selectedMembers, [$state]));
                    }),
            ])
            ->statePath('data');
    }

    #[Computed]
    public function availableStudents()
    {
        return $this->section
            ->students()
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

        return $this->section
            ->students()
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
            Notification::make()->title('Validation Error')->body('Please fill in all required fields.')->danger()->send();

            return;
        }

        // Add leader and selected members
        $members = $this->selectedMembers;
        $members[] = $data['leader_id'];
        $members = array_unique($members);

        $groupService = app(GroupService::class);
        $feeService = app(FeeService::class);

        $group = $groupService->create([
            'name' => $data['name'],
            'section_id' => $this->section->id,
            'leader_id' => $data['leader_id'],
            'member_ids' => $members,
        ]);

        // Initialize group fee ledger
        $feeService->initializeGroupLedger($group);

        Notification::make()
            ->title('Group created successfully')
            ->body('The research group has been created with ' . count($members) . ' member(s).')
            ->success()
            ->send();

        $this->redirect(route($this->routePrefix . '.classes.view', ['section' => $this->section->id, 'tab' => 'groups']), navigate: true);
    }
};
?>

<x-slot name="title">
    Create Group - {{ $section->name }}
</x-slot>

@assets
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="min-h-screen bg-slate-50">

    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-slate-900 px-4 py-8 md:px-6 md:py-10">
        <div class="pointer-events-none absolute inset-0"
            style="background-image: radial-gradient(circle, rgba(255,255,255,0.035) 1px, transparent 1px); background-size: 28px 28px;">
        </div>
        <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full blur-[120px]"
            style="background: radial-gradient(circle, rgba(0,82,255,0.18) 0%, transparent 70%);"></div>

        <div class="relative mx-auto max-w-7xl">
            {{-- Breadcrumb --}}
            <div class="mb-4 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route($this->routePrefix . '.classes') }}" wire:navigate
                    class="transition-colors hover:text-blue-400">My Classes</a>
                <span>/</span>
                <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $section->id]) }}" wire:navigate
                    class="transition-colors hover:text-blue-400">{{ $section->name }}</a>
                <span>/</span>
                <span class="text-slate-200">Create Group</span>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white md:text-3xl">Create Research Group</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $section->name }} &bull; {{ $section->semester->name }}
                    </p>
                </div>

                <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $section->id, 'tab' => 'groups']) }}"
                    wire:navigate
                    class="inline-flex items-center gap-2 self-start rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm transition-all hover:bg-white/15">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                    Cancel
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-5 md:px-6 md:py-6">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-6">

            {{-- Group Form --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-semibold text-slate-800">Group Information</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Enter the basic details for the research group.</p>
                    </div>

                    <div class="p-5">
                        <form wire:submit="create">
                            {{ $this->form }}

                            <div class="mt-5 border-t border-slate-100 pt-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Selected Members</span>
                                    <span class="font-semibold text-slate-900"
                                        x-text="$wire.selectedMembers.length + {{ isset($data['leader_id']) && $data['leader_id'] ? 1 : 0 }}"></span>
                                </div>
                                @if (isset($data['leader_id']) && $data['leader_id'])
                                    <p class="mt-2 text-xs text-slate-400">
                                        <x-heroicon-o-information-circle class="inline h-3.5 w-3.5" />
                                        The group leader is automatically included as a member.
                                    </p>
                                @endif
                            </div>

                            <button type="submit"
                                class="mt-5 w-full rounded-xl py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-px disabled:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50"
                                style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%);"
                                @disabled(empty($data['name']) || empty($data['leader_id']))>
                                <span class="flex items-center justify-center gap-2">
                                    <x-heroicon-o-user-group class="h-4 w-4" />
                                    Create Group
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Member Selector --}}
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-semibold text-slate-800">Select Members</h2>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Choose students from {{ $section->name }} to add to this group.
                            @if (isset($data['leader_id']) && $data['leader_id'])
                                The group leader is automatically included.
                            @endif
                        </p>
                    </div>

                    <div class="p-5" x-data="{
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
                        @if (count($this->availableMembers) === 0)
                            <div class="py-12 text-center">
                                <div
                                    class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                                    <x-heroicon-o-users class="h-6 w-6 text-blue-400" />
                                </div>
                                <p class="mb-1 text-sm font-semibold text-slate-700">
                                    @if (isset($data['leader_id']) && $data['leader_id'])
                                        No other students available
                                    @else
                                        Select a group leader first
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400">
                                    @if (isset($data['leader_id']) && $data['leader_id'])
                                        All students in this section are already assigned to groups.
                                    @else
                                        Choose a leader from the form on the left to see available members.
                                    @endif
                                </p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                @foreach ($this->availableMembers as $id => $student)
                                    <div wire:key="student-{{ $id }}"
                                        @click="toggleMember({{ $id }})"
                                        :class="isSelected({{ $id }}) ?
                                            'border-blue-400 bg-blue-50 ring-2 ring-blue-100' :
                                            'border-slate-200 bg-white hover:border-blue-200 hover:bg-blue-50/40'"
                                        class="relative cursor-pointer overflow-hidden rounded-xl border p-3.5 transition-all">
                                        <div class="flex items-center gap-3">
                                            <div :class="isSelected({{ $id }}) ? 'border-blue-600 bg-blue-600' :
                                                'border-slate-300 bg-white'"
                                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition-colors">
                                                <x-heroicon-o-check x-show="isSelected({{ $id }})"
                                                    class="h-3 w-3 text-white" style="stroke-width: 3;" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-900">
                                                    {{ $student['name'] }}</p>
                                                <p class="text-xs text-slate-400">{{ $student['student_number'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex gap-2">
                                <button type="button" @click="selectAll()"
                                    class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100">
                                    Select All ({{ count($this->availableMembers) }})
                                </button>
                                <button type="button" @click="deselectAll()"
                                    class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
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
