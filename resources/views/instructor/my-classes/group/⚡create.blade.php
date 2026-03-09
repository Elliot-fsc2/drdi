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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

<div class="relative min-h-screen" style="background: #F8FAFC">

    {{-- Ambient background glows --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 h-[400px] w-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Page Header ────────────────────────────── --}}
        <div class="mb-8">
            {{-- Breadcrumb --}}
            <div class="mb-5 flex items-center gap-2" style="font-size: 12px; color: #94A3B8">
                <a href="{{ route($this->routePrefix . '.classes') }}" wire:navigate
                    class="transition-colors hover:underline" style="color: #64748B">My Classes</a>
                <span style="color: #CBD5E1">/</span>
                <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $section->id]) }}" wire:navigate
                    class="transition-colors hover:underline" style="color: #64748B">{{ $section->name }}</a>
                <span style="color: #CBD5E1">/</span>
                <span style="color: #0052FF; font-weight: 500">Create Group</span>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                            New Group
                        </span>
                    </div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.6rem, 3.5vw, 2.25rem); letter-spacing: -0.015em; color: #0F172A">
                        Create Research Group<span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        {{ $section->name }} &bull; {{ $section->semester->name }}
                    </p>
                </div>

                <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $section->id, 'tab' => 'groups']) }}"
                    wire:navigate
                    class="inline-flex items-center gap-2 self-start rounded-xl border px-4 py-2 text-sm font-medium transition-all hover:-translate-y-px hover:shadow-md"
                    style="border-color: #E2E8F0; background: white; color: #374151; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                    Cancel
                </a>
            </div>
        </div>

        {{-- ── Main Grid ──────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-6">

            {{-- Group Form --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 overflow-hidden rounded-2xl border"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="border-b px-5 py-4" style="border-color: #F1F5F9">
                        <h2 class="text-sm font-semibold" style="color: #0F172A">Group Information</h2>
                        <p class="mt-0.5 text-xs" style="color: #94A3B8">Enter the basic details for the research group.
                        </p>
                    </div>

                    <div class="p-5">
                        <form wire:submit="create">
                            {{ $this->form }}

                            <div class="mt-5 border-t pt-5" style="border-color: #F1F5F9">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748B">Selected Members</span>
                                    <span class="font-semibold" style="color: #0F172A"
                                        x-text="$wire.selectedMembers.length + {{ isset($data['leader_id']) && $data['leader_id'] ? 1 : 0 }}"></span>
                                </div>
                                @if (isset($data['leader_id']) && $data['leader_id'])
                                    <p class="mt-2 text-xs" style="color: #94A3B8">
                                        <x-heroicon-o-information-circle class="inline h-3.5 w-3.5" />
                                        The group leader is automatically included as a member.
                                    </p>
                                @endif
                            </div>

                            <button type="submit"
                                class="mt-5 w-full rounded-xl py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-px disabled:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50"
                                style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 14px rgba(0,82,255,0.25)"
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
                <div class="overflow-hidden rounded-2xl border"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="border-b px-5 py-4" style="border-color: #F1F5F9">
                        <h2 class="text-sm font-semibold" style="color: #0F172A">Select Members</h2>
                        <p class="mt-0.5 text-xs" style="color: #94A3B8">
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
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl"
                                    style="background: rgba(0,82,255,0.06)">
                                    <x-heroicon-o-users class="h-6 w-6" style="color: #0052FF" />
                                </div>
                                <p class="mb-1 text-sm font-semibold" style="color: #0F172A">
                                    @if (isset($data['leader_id']) && $data['leader_id'])
                                        No other students available
                                    @else
                                        Select a group leader first
                                    @endif
                                </p>
                                <p class="text-xs" style="color: #94A3B8">
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
                                            'ring-2' :
                                            ''"
                                        :style="isSelected({{ $id }}) ?
                                            'border-color: #0052FF; background: rgba(0,82,255,0.05); box-shadow: 0 0 0 2px rgba(0,82,255,0.15)' :
                                            'border-color: #E2E8F0; background: white'"
                                        class="relative cursor-pointer overflow-hidden rounded-xl border p-3.5 transition-all duration-150 hover:shadow-md"
                                        style="border-color: #E2E8F0; background: white">
                                        <div class="flex items-center gap-3">
                                            <div :style="isSelected({{ $id }}) ?
                                                'border-color: #0052FF; background: #0052FF' :
                                                'border-color: #CBD5E1; background: white'"
                                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition-colors"
                                                style="border-color: #CBD5E1; background: white">
                                                <x-heroicon-o-check x-show="isSelected({{ $id }})"
                                                    class="h-3 w-3 text-white" style="stroke-width: 3;" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold" style="color: #0F172A">
                                                    {{ $student['name'] }}</p>
                                                <p class="text-xs" style="color: #94A3B8">
                                                    {{ $student['student_number'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex gap-2">
                                <button type="button" @click="selectAll()"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors hover:opacity-80"
                                    style="border-color: rgba(0,82,255,0.25); background: rgba(0,82,255,0.06); color: #0052FF">
                                    Select All ({{ count($this->availableMembers) }})
                                </button>
                                <button type="button" @click="deselectAll()"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors hover:opacity-80"
                                    style="border-color: #E2E8F0; background: #F1F5F9; color: #64748B">
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
