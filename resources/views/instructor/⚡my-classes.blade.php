<?php

use App\Models\Program;
use App\Models\Section;
use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\Url;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Classes')] class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    #[Url]
    public $search = '';
    public $semester = '2nd Semester 2025-2026';

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    public function createSectionAction(): Action
    {
        return Action::make('createSection')
            ->modalWidth('lg')
            ->color('success')
            ->modalCloseButton(false)
            ->label('Create Section')
            ->icon(Heroicon::Plus)
            ->color('primary')
            ->form([
                TextInput::make('name')->label('Section Name')->placeholder('e.g., BSCS-4A')->required()->maxLength(255),

                Select::make('program_id')->label('Program')->options(Program::pluck('name', 'id'))->required()->searchable(),

                Select::make('semester_id')
                    ->label('Semester')
                    ->options(Semester::active()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
            ])
            ->successNotificationTitle('Section created successfully')
            ->action(function (array $data): void {
                Section::create([
                    'name' => $data['name'],
                    'program_id' => $data['program_id'],
                    'semester_id' => $data['semester_id'],
                    'instructor_id' => auth()->user()->profileable->id,
                ]);

                unset($this->classes);
            });
    }

    #[Computed]
    public function classes()
    {
        $query = Section::where('instructor_id', auth()->user()->profileable->id)
            ->whereHas('semester', function ($query) {
                $query->active();
            })
            ->withCount('students')
            ->withCount('groups')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            });

        return $query->get()->map(function ($section) {
            return [
                'id' => $section->id,
                'section' => $section->name,
                'course' => $section->program->name,
                'students_count' => $section->students_count,
                'groups_count' => $section->groups_count,
            ];
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

<div class="min-h-screen relative" style="background: #F8FAFC">

    {{-- Ambient background glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">

        {{-- ── Page Header ────────────────────────────────────────────────────────── --}}
        <div class="mb-8 sm:mb-10">

            {{-- Semester badge --}}
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    {{ $semester }}
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        My
                        <span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                            Classes
                        </span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        Manage your assigned sections and track student progress.
                    </p>
                </div>

                {{-- Action bar --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button wire:click="mountAction('createSection')"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98] group shrink-0"
                        style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.3)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Create Section
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="relative">
                        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search sections…"
                            class="pl-10 pr-4 h-10 rounded-xl text-sm outline-none transition-all duration-200 w-full sm:w-60"
                            style="background: white; border: 1px solid #E2E8F0; color: #0F172A"
                            onfocus="this.style.borderColor='#0052FF'; this.style.boxShadow='0 0 0 3px rgba(0,82,255,0.12)'"
                            onblur="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'" />
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                            style="color: #94A3B8">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Empty State ────────────────────────────────────────────────────────── --}}
        @if (count($this->classes) === 0)
            <div class="bg-white rounded-2xl border flex flex-col items-center justify-center py-20 px-8 text-center"
                style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6"
                    style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 8px 24px rgba(0,82,255,0.3)">
                    <x-heroicon-o-academic-cap class="h-10 w-10 text-white" />
                </div>
                <h3 class="mb-2" style="font-family: 'Calistoga', Georgia, serif; font-size: 1.5rem; color: #0F172A">
                    @if ($this->search)
                        No results found
                    @else
                        No classes yet
                    @endif
                </h3>
                <p class="text-sm max-w-xs mb-6" style="color: #64748B; line-height: 1.6">
                    @if ($this->search)
                        No sections match "<span style="color: #0052FF; font-weight: 600">{{ $this->search }}</span>".
                        Try a different search term.
                    @else
                        Create your first section to start managing students, groups, and proposals.
                    @endif
                </p>
                @unless ($this->search)
                    <button wire:click="mountAction('createSection')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                        style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.3)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Create your first section
                    </button>
                @endunless
            </div>
        @else
            {{-- ── Stats summary ─────────────────────────────────────────────────────── --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mb-6 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background: #0052FF; animation: pulse 2s infinite"></span>
                    <span class="text-sm font-semibold" style="color: #0F172A">
                        {{ count($this->classes) }} {{ count($this->classes) === 1 ? 'section' : 'sections' }}
                    </span>
                </div>
                <div class="w-px h-4 hidden sm:block" style="background: #E2E8F0"></div>
                <span class="text-sm" style="color: #64748B">
                    {{ collect($this->classes)->sum('students_count') }} total students
                </span>
                <div class="w-px h-4 hidden sm:block" style="background: #E2E8F0"></div>
                <span class="text-sm" style="color: #64748B">
                    {{ collect($this->classes)->sum('groups_count') }} total groups
                </span>
                @if ($this->search)
                    <div class="w-px h-4 hidden sm:block" style="background: #E2E8F0"></div>
                    <span class="text-sm" style="color: #64748B">
                        Filtered by "<span style="color: #0052FF; font-weight: 500">{{ $this->search }}</span>"
                    </span>
                @endif
            </div>

            {{-- ── Classes Grid ──────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($this->classes as $class)
                    <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $class['id']]) }}"
                        wire:key="{{ $class['section'] }}" wire:navigate class="group block h-full">

                        @if ($loop->first)
                            {{-- Featured card — gradient border treatment --}}
                            <div class="relative p-[2px] rounded-2xl h-full transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-0.5"
                                style="background: linear-gradient(135deg, #0052FF, #4D7CFF)">
                                <div class="bg-white rounded-[14px] overflow-hidden h-full flex flex-col">

                                    <div class="px-5 pt-5 pb-4 flex-1">
                                        {{-- Active badge --}}
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full mb-4"
                                            style="background: rgba(0,82,255,0.08); border: 1px solid rgba(0,82,255,0.18)">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                style="background: #0052FF; animation: pulse 2s infinite"></span>
                                            <span
                                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                                                Active
                                            </span>
                                        </div>

                                        <h3 class="font-bold text-base leading-snug mb-1 transition-colors duration-200 group-hover:text-blue-600"
                                            style="color: #0F172A">
                                            {{ $class['section'] }}
                                        </h3>
                                        <p class="text-sm leading-snug" style="color: #64748B">{{ $class['course'] }}
                                        </p>
                                    </div>

                                    {{-- Stats footer --}}
                                    <div class="px-5 py-4 border-t"
                                        style="border-color: #EFF3FF; background: #F5F8FF">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1">
                                                <div class="mb-1"
                                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                                    Students
                                                </div>
                                                <div class="font-bold text-2xl" style="color: #0052FF">
                                                    {{ $class['students_count'] }}</div>
                                            </div>
                                            <div class="w-px h-9" style="background: #DBEAFE"></div>
                                            <div class="flex-1">
                                                <div class="mb-1"
                                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                                    Groups
                                                </div>
                                                <div class="font-bold text-2xl" style="color: #0052FF">
                                                    {{ $class['groups_count'] }}</div>
                                            </div>
                                            <div class="transition-transform duration-200 group-hover:translate-x-1"
                                                style="color: #93C5FD">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Standard card --}}
                            <div class="bg-white rounded-2xl border overflow-hidden h-full flex flex-col transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-0.5"
                                style="border-color: #E2E8F0">

                                {{-- Thin accent stripe --}}
                                <div class="h-[3px] w-full transition-opacity duration-300"
                                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); opacity: 0.25"
                                    x-data x-init="$el.parentElement.addEventListener('mouseenter', () => $el.style.opacity = '1');
                                    $el.parentElement.addEventListener('mouseleave', () => $el.style.opacity = '0.25')">
                                </div>

                                <div class="px-5 pt-4 pb-4 flex-1 relative overflow-hidden">
                                    {{-- Hover gradient overlay --}}
                                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                                        style="background: linear-gradient(135deg, rgba(0,82,255,0.03), transparent)">
                                    </div>

                                    <h3 class="font-bold text-base leading-snug mb-1 relative transition-colors duration-200 group-hover:text-blue-600"
                                        style="color: #0F172A">
                                        {{ $class['section'] }}
                                    </h3>
                                    <p class="text-sm leading-snug relative" style="color: #64748B">
                                        {{ $class['course'] }}</p>
                                </div>

                                {{-- Stats footer --}}
                                <div class="px-5 py-4 border-t" style="border-color: #F1F5F9; background: #FAFAFA">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1">
                                            <div class="mb-1"
                                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                                Students
                                            </div>
                                            <div class="font-bold text-xl transition-colors duration-200 group-hover:text-blue-600"
                                                style="color: #0F172A">
                                                {{ $class['students_count'] }}
                                            </div>
                                        </div>
                                        <div class="w-px h-8" style="background: #E2E8F0"></div>
                                        <div class="flex-1">
                                            <div class="mb-1"
                                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                                Groups
                                            </div>
                                            <div class="font-bold text-xl transition-colors duration-200 group-hover:text-blue-600"
                                                style="color: #0F172A">
                                                {{ $class['groups_count'] }}
                                            </div>
                                        </div>
                                        <div class="transition-all duration-200 group-hover:translate-x-0.5"
                                            style="color: #CBD5E1" x-data x-init="$el.parentElement.parentElement.parentElement.addEventListener('mouseenter', () => $el.style.color = '#93C5FD');
                                            $el.parentElement.parentElement.parentElement.addEventListener('mouseleave', () => $el.style.color = '#CBD5E1')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </a>
                @endforeach
            </div>
        @endif

    </div>
    <x-filament-actions::modals />
</div>
