<?php

use App\Enums\PersonnelRole;
use App\Models\Program;
use App\Models\ResearchLibrary;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Research Repository')] class extends Component {
    public string $search = '';
    public string $filterYear = '';
    public string $filterProgram = '';

    #[Computed]
    public function libraries()
    {
        return ResearchLibrary::where('is_published', true)
            ->with(['group.section.program', 'group.leader', 'group.members', 'group.personnel.instructor'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('abstract', 'like', '%' . $this->search . '%')
                        ->orWhereHas('group.leader', function ($q2) {
                            $q2->where('first_name', 'like', '%' . $this->search . '%')->orWhere('last_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterYear, fn($q) => $q->where('academic_year', $this->filterYear))
            ->when($this->filterProgram, function ($query) {
                $query->whereHas('group.section.program', fn($q) => $q->where('name', $this->filterProgram));
            })
            ->orderByDesc('published_at')
            ->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return ResearchLibrary::where('is_published', true)->count();
    }

    #[Computed]
    public function availableYears()
    {
        return ResearchLibrary::where('is_published', true)->distinct()->orderByDesc('academic_year')->pluck('academic_year');
    }

    #[Computed]
    public function availablePrograms()
    {
        return Program::whereHas('sections.groups.researchLibrary', fn($q) => $q->where('is_published', true))->orderBy('name')->pluck('name');
    }
};
?>

@assets
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
        <div class="mb-8 sm:mb-10">
            <div class="mb-5 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Research Repository
                </span>
            </div>

            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                Research Repository<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                Browse all completed and approved research group projects.
            </p>
        </div>

        {{-- ── Filters ─────────────────────────────────── --}}
        <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-center">

            {{-- Search --}}
            <div class="relative flex-1">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by title, leader, or abstract…"
                    class="w-full rounded-xl border py-2.5 pl-4 pr-4 text-sm outline-none transition-all focus:ring-2"
                    style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04); --tw-ring-color: rgba(0,82,255,0.15)">
            </div>

            {{-- Year filter --}}
            <select wire:model.live="filterYear"
                class="rounded-xl border py-2.5 pl-3.5 pr-8 text-sm outline-none transition-all focus:ring-2"
                style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04); --tw-ring-color: rgba(0,82,255,0.15)">
                <option value="">All Years</option>
                @foreach ($this->availableYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>

            {{-- Program filter --}}
            <select wire:model.live="filterProgram"
                class="rounded-xl border py-2.5 pl-3.5 pr-8 text-sm outline-none transition-all focus:ring-2"
                style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04); --tw-ring-color: rgba(0,82,255,0.15)">
                <option value="">All Programs</option>
                @foreach ($this->availablePrograms as $program)
                    <option value="{{ $program }}">{{ $program }}</option>
                @endforeach
            </select>

            {{-- Count badge --}}
            <div class="flex-shrink-0 rounded-xl border px-4 py-2.5 text-sm font-semibold"
                style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04)">
                <span style="color: #0052FF">{{ $this->libraries->count() }}</span>
                <span style="color: #94A3B8"> / {{ $this->totalCount }}</span>
            </div>
        </div>

        {{-- ── Cards Grid ───────────────────────────────── --}}
        @if ($this->libraries->isEmpty())
            <div class="rounded-2xl border py-16 text-center"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <p class="mb-1 font-semibold" style="color: #0F172A">No results found</p>
                <p class="text-sm" style="color: #94A3B8">Try adjusting your search or filter criteria.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->libraries as $library)
                    @php
                        $group = $library->group;
                        $section = $group?->section;
                        $program = $section?->program;
                        $leader = $group?->leader;
                        $members = $group?->members ?? collect();
                        $adviser = $group?->personnel->firstWhere('role', \App\Enums\PersonnelRole::TECHNICAL_ADVISER)
                            ?->instructor;
                    @endphp
                    <a href="{{ route('repository.details', ['index' => $library->id]) }}" wire:navigate
                        class="group relative flex flex-col overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-xl"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-decoration: none">

                        {{-- Gradient top stripe --}}
                        <div class="h-[3px] w-full rounded-t-2xl"
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

                        <div class="flex flex-1 flex-col p-5">

                            {{-- Header: program + year badges --}}
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                @if ($program)
                                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em">
                                        {{ $program->name }}
                                    </span>
                                @endif
                                @if ($section)
                                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                        style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                                        {{ $section->name }} &bull; {{ $library->academic_year }}
                                    </span>
                                @else
                                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                        style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                                        {{ $library->academic_year }}
                                    </span>
                                @endif
                                <span
                                    class="ml-auto inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                    style="border-color: rgba(16,185,129,0.25); background: rgba(16,185,129,0.07); color: #059669">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background: #10B981"></span>
                                    Published
                                </span>
                            </div>

                            {{-- Title --}}
                            <h3 class="mb-2 text-[0.9375rem] font-semibold leading-snug" style="color: #0F172A">
                                {{ $library->title }}
                            </h3>

                            {{-- Abstract --}}
                            <p class="mb-4 line-clamp-3 flex-1 text-sm leading-relaxed" style="color: #64748B">
                                {{ $library->abstract }}
                            </p>

                            {{-- Divider --}}
                            <div class="mb-4 h-px" style="background: #F1F5F9"></div>

                            {{-- Leader --}}
                            @if ($leader)
                                <div class="mb-3">
                                    <div class="mb-1.5 flex items-center gap-1.5">
                                        <span class="text-xs font-semibold"
                                            style="color: #94A3B8; letter-spacing: 0.05em; text-transform: uppercase; font-family: 'JetBrains Mono', monospace; font-size: 10px">Leader</span>
                                    </div>
                                    <p class="text-sm font-medium" style="color: #0F172A">
                                        {{ $leader->first_name . ' ' . $leader->last_name }}
                                    </p>
                                </div>
                            @endif

                            @if ($members->isNotEmpty())
                                <div>
                                    <div class="mb-1.5 flex items-center gap-1.5">
                                        <span class="text-xs font-semibold"
                                            style="color: #94A3B8; letter-spacing: 0.05em; text-transform: uppercase; font-family: 'JetBrains Mono', monospace; font-size: 10px">Members</span>
                                    </div>
                                    <p class="text-sm" style="color: #64748B">
                                        {{ $members->map(fn($m) => $m->first_name . ' ' . $m->last_name)->implode(', ') }}
                                    </p>
                                </div>
                            @endif

                        </div>

                        {{-- Footer: adviser --}}
                        @if ($adviser)
                            <div class="flex items-center gap-2.5 border-t px-5 py-3"
                                style="border-color: #F1F5F9; background: #FAFBFF">
                                <span class="text-xs" style="color: #64748B">
                                    Advised by <span class="font-semibold"
                                        style="color: #0F172A">{{ $adviser->full_name }}</span>
                                </span>
                            </div>
                        @endif

                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
