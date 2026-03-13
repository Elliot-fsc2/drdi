<?php

use App\Models\Group;
use App\Models\Section;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    public Section $section;

    #[Url]
    public $tab = 'groups';

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    #[Computed]
    public function classData(): array
    {
        return [
            'id' => $this->section->id,
            'section' => $this->section->name,
            'course' => 'Thesis 1',
            'semester' => '2nd Semester 2025-2026',
            'students_count' => 32,
            'groups_count' => 8,
        ];
    }

    #[Computed]
    public function groups()
    {
        return Group::where('section_id', $this->section->id)
            ->whereHas('section', function ($query) {
                $query->where('instructor_id', auth()->user()->profileable->id);
            })
            ->with(['leader', 'proposals'])
            ->withCount('members')
            ->get();
    }
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

<x-slot name="title">{{ $section->name }} – {{ $section->semester->name }}</x-slot>

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

        {{-- ── Breadcrumb ──────────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-2 mb-6" style="font-size: 12px">
            <a href="{{ route($this->routePrefix . '.classes') }}" wire:navigate
                class="transition-colors hover:text-blue-600" style="color: #94A3B8">My Classes</a>
            <svg class="h-3 w-3" style="color: #CBD5E1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span style="color: #64748B">{{ $this->classData['section'] }}</span>
        </div>

        {{-- ── Page Header ────────────────────────────────────────────────────────── --}}
        <div class="mb-8 sm:mb-10">

            {{-- Course badge --}}
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    {{ $this->classData['course'] }}
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        {{ $this->classData['section'] }}
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">{{ $this->classData['semester'] }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Pill tab switcher --}}
                    <div class="inline-flex items-center gap-1 rounded-xl p-1"
                        style="background: #EEF2FF; border: 1px solid rgba(0,82,255,0.12)">
                        @foreach ([['key' => 'groups', 'label' => 'Groups', 'icon' => 'rectangle-group'], ['key' => 'students', 'label' => 'Students', 'icon' => 'users'], ['key' => 'schedule', 'label' => 'Schedule', 'icon' => 'calendar-days']] as $t)
                            <a href="?tab={{ $t['key'] }}" wire:navigate
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                                style="{{ $tab === $t['key'] ? 'background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.3)' : 'color: #64748B' }}">
                                <x-dynamic-component :component="'heroicon-o-' . $t['icon']" class="h-4 w-4" />
                                {{ $t['label'] }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Add Group button (groups tab only) --}}
                    @if ($tab === 'groups')
                        <a href="{{ route($this->routePrefix . '.classes.group.create', ['section' => $section->id]) }}"
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5"
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.3)">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Add Group
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Content + Sidebar ──────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

            {{-- Main panel --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl border"
                    style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    {{-- Top gradient stripe --}}
                    <div class="h-[3px] w-full rounded-t-2xl"
                        style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

                    {{-- ── Groups Tab ──────────────────────────────────────────────────── --}}
                    @if ($tab === 'groups')
                        <div class="p-5 md:p-6">
                            @if (count($this->groups) === 0)
                                <div class="flex flex-col items-center justify-center py-16 px-8 text-center">
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
                                        style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 8px 24px rgba(0,82,255,0.3)">
                                        <x-heroicon-o-user-group class="h-8 w-8 text-white" />
                                    </div>
                                    <h3 class="mb-2"
                                        style="font-family: 'Calistoga', Georgia, serif; font-size: 1.35rem; color: #0F172A">
                                        No research groups yet</h3>
                                    <p class="text-sm mb-5 max-w-xs" style="color: #64748B; line-height: 1.6">
                                        Create the first research group for this section.</p>
                                    <a href="{{ route($this->routePrefix . '.classes.group.create', ['section' => $section->id]) }}"
                                        wire:navigate
                                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white"
                                        style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.25)">
                                        <x-heroicon-o-plus class="h-4 w-4" />
                                        Create First Group
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    @foreach ($this->groups as $group)
                                        @php
                                            $approvedProposal = $group->proposals->first(function ($p) {
                                                $status =
                                                    $p->status instanceof \App\Enums\ProposalStatus
                                                        ? $p->status->value
                                                        : strtolower($p->status);
                                                return $status === 'approved';
                                            });
                                            $latestProposal =
                                                $approvedProposal ??
                                                $group->proposals->sortByDesc('created_at')->first();
                                            $statusValue = $latestProposal
                                                ? ($latestProposal->status instanceof \App\Enums\ProposalStatus
                                                    ? $latestProposal->status->value
                                                    : strtolower($latestProposal->status))
                                                : null;
                                        @endphp
                                        <a href="{{ route($this->routePrefix . '.classes.group.view', ['section' => $this->classData['id'], 'group' => $group->id]) }}"
                                            wire:navigate class="group block h-full">

                                            @if ($loop->first)
                                                {{-- Featured card — gradient border --}}
                                                <div class="relative p-[2px] rounded-2xl h-full transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-0.5"
                                                    style="background: linear-gradient(135deg, #0052FF, #4D7CFF)">
                                                    <div
                                                        class="bg-white rounded-[14px] overflow-hidden h-full flex flex-col">
                                                        <div class="px-5 pt-5 pb-4 flex-1">
                                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full mb-3"
                                                                        style="background: rgba(0,82,255,0.08); border: 1px solid rgba(0,82,255,0.18)">
                                                                        <span class="w-1.5 h-1.5 rounded-full"
                                                                            style="background: #0052FF; animation: pulse 2s infinite"></span>
                                                                        <span
                                                                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">Featured</span>
                                                                    </div>
                                                                    <h3 class="font-bold text-base leading-snug mb-1 transition-colors duration-200 group-hover:text-blue-600"
                                                                        style="color: #0F172A">
                                                                        {{ $group->name }}
                                                                    </h3>
                                                                    @if ($group->leader_id)
                                                                        <p class="text-xs" style="color: #64748B">Led
                                                                            by
                                                                            {{ $group->leader->first_name }}
                                                                            {{ $group->leader->last_name }}</p>
                                                                    @else
                                                                        <p class="text-xs italic"
                                                                            style="color: #94A3B8">No leader assigned
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                                <div class="flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                                                    style="background: rgba(0,82,255,0.08); border: 1px solid rgba(0,82,255,0.15); color: #0052FF">
                                                                    <x-heroicon-o-users class="h-3.5 w-3.5" />
                                                                    {{ $group->members_count }}
                                                                </div>
                                                            </div>

                                                            @if ($latestProposal)
                                                                @php
                                                                    $statusStyles = match ($statusValue) {
                                                                        'approved' => [
                                                                            'bg' => 'rgba(16,185,129,0.08)',
                                                                            'border' => 'rgba(16,185,129,0.2)',
                                                                            'color' => '#059669',
                                                                            'dot' => '#10B981',
                                                                            'label' => 'Approved',
                                                                        ],
                                                                        'pending' => [
                                                                            'bg' => 'rgba(245,158,11,0.08)',
                                                                            'border' => 'rgba(245,158,11,0.2)',
                                                                            'color' => '#B45309',
                                                                            'dot' => '#F59E0B',
                                                                            'label' => 'Pending',
                                                                        ],
                                                                        'rejected' => [
                                                                            'bg' => 'rgba(239,68,68,0.08)',
                                                                            'border' => 'rgba(239,68,68,0.2)',
                                                                            'color' => '#DC2626',
                                                                            'dot' => '#EF4444',
                                                                            'label' => 'Rejected',
                                                                        ],
                                                                        default => [
                                                                            'bg' => 'rgba(148,163,184,0.08)',
                                                                            'border' => 'rgba(148,163,184,0.2)',
                                                                            'color' => '#64748B',
                                                                            'dot' => '#94A3B8',
                                                                            'label' => ucfirst(
                                                                                $statusValue ?? 'Unknown',
                                                                            ),
                                                                        ],
                                                                    };
                                                                @endphp
                                                                <div class="border-t pt-3"
                                                                    style="border-color: #EFF3FF">
                                                                    <p class="truncate text-xs mb-2"
                                                                        style="color: #64748B"
                                                                        title="{{ $latestProposal->title }}">
                                                                        {{ $latestProposal->title }}</p>
                                                                    <span
                                                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                                                        style="background: {{ $statusStyles['bg'] }}; border: 1px solid {{ $statusStyles['border'] }}; color: {{ $statusStyles['color'] }}">
                                                                        <span class="w-1.5 h-1.5 rounded-full"
                                                                            style="background: {{ $statusStyles['dot'] }}"></span>
                                                                        {{ $statusStyles['label'] }}
                                                                    </span>
                                                                </div>
                                                            @else
                                                                <div class="border-t pt-3"
                                                                    style="border-color: #EFF3FF">
                                                                    <p class="text-xs italic" style="color: #CBD5E1">
                                                                        No proposal submitted
                                                                        yet</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="px-5 py-3 border-t flex items-center justify-end"
                                                            style="border-color: #EFF3FF; background: #F5F8FF">
                                                            <div class="flex items-center gap-1 text-xs font-semibold transition-transform duration-200 group-hover:translate-x-1"
                                                                style="color: #0052FF">
                                                                View Group
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                                                    fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Standard card --}}
                                                <div class="bg-white rounded-2xl border overflow-hidden h-full flex flex-col transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-0.5"
                                                    style="border-color: #E2E8F0">
                                                    <div class="h-[3px] w-full transition-opacity duration-300"
                                                        style="background: linear-gradient(to right, #0052FF, #4D7CFF); opacity: 0.25"
                                                        x-data x-init="$el.parentElement.addEventListener('mouseenter', () => $el.style.opacity = '1');
                                                        $el.parentElement.addEventListener('mouseleave', () => $el.style.opacity = '0.25')">
                                                    </div>
                                                    <div class="px-5 pt-4 pb-3 flex-1 relative overflow-hidden">
                                                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                                                            style="background: linear-gradient(135deg, rgba(0,82,255,0.03), transparent)">
                                                        </div>
                                                        <div
                                                            class="flex items-start justify-between gap-3 mb-2 relative">
                                                            <div class="flex-1 min-w-0">
                                                                <h3 class="font-bold text-base leading-snug mb-1 transition-colors duration-200 group-hover:text-blue-600"
                                                                    style="color: #0F172A">
                                                                    {{ $group->name }}
                                                                </h3>
                                                                @if ($group->leader_id)
                                                                    <p class="text-xs" style="color: #64748B">Led
                                                                        by {{ $group->leader->first_name }}
                                                                        {{ $group->leader->last_name }}</p>
                                                                @else
                                                                    <p class="text-xs italic" style="color: #94A3B8">
                                                                        No leader
                                                                        assigned</p>
                                                                @endif
                                                            </div>
                                                            <div class="flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs"
                                                                style="background: #F8FAFC; border: 1px solid #E2E8F0; color: #64748B">
                                                                <x-heroicon-o-users class="h-3.5 w-3.5" />
                                                                {{ $group->members_count }}
                                                            </div>
                                                        </div>

                                                        @if ($latestProposal)
                                                            @php
                                                                $statusStyles = match ($statusValue) {
                                                                    'approved' => [
                                                                        'bg' => 'rgba(16,185,129,0.08)',
                                                                        'border' => 'rgba(16,185,129,0.2)',
                                                                        'color' => '#059669',
                                                                        'dot' => '#10B981',
                                                                        'label' => 'Approved',
                                                                    ],
                                                                    'pending' => [
                                                                        'bg' => 'rgba(245,158,11,0.08)',
                                                                        'border' => 'rgba(245,158,11,0.2)',
                                                                        'color' => '#B45309',
                                                                        'dot' => '#F59E0B',
                                                                        'label' => 'Pending',
                                                                    ],
                                                                    'rejected' => [
                                                                        'bg' => 'rgba(239,68,68,0.08)',
                                                                        'border' => 'rgba(239,68,68,0.2)',
                                                                        'color' => '#DC2626',
                                                                        'dot' => '#EF4444',
                                                                        'label' => 'Rejected',
                                                                    ],
                                                                    default => [
                                                                        'bg' => 'rgba(148,163,184,0.08)',
                                                                        'border' => 'rgba(148,163,184,0.2)',
                                                                        'color' => '#64748B',
                                                                        'dot' => '#94A3B8',
                                                                        'label' => ucfirst($statusValue ?? 'Unknown'),
                                                                    ],
                                                                };
                                                            @endphp
                                                            <div class="border-t pt-3 relative"
                                                                style="border-color: #F1F5F9">
                                                                <p class="truncate text-xs mb-2"
                                                                    style="color: #64748B"
                                                                    title="{{ $latestProposal->title }}">
                                                                    {{ $latestProposal->title }}</p>
                                                                <span
                                                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                                                    style="background: {{ $statusStyles['bg'] }}; border: 1px solid {{ $statusStyles['border'] }}; color: {{ $statusStyles['color'] }}">
                                                                    <span class="w-1.5 h-1.5 rounded-full"
                                                                        style="background: {{ $statusStyles['dot'] }}"></span>
                                                                    {{ $statusStyles['label'] }}
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="border-t pt-3 relative"
                                                                style="border-color: #F1F5F9">
                                                                <p class="text-xs italic" style="color: #CBD5E1">No
                                                                    proposal submitted yet</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="px-5 py-3 border-t flex items-center justify-end"
                                                        style="border-color: #F1F5F9; background: #FAFAFA">
                                                        <div class="flex items-center gap-1 text-xs font-medium transition-all duration-200 group-hover:translate-x-0.5"
                                                            style="color: #94A3B8" x-data x-init="$el.parentElement.parentElement.parentElement.addEventListener('mouseenter', () => $el.style.color = '#0052FF');
                                                            $el.parentElement.parentElement.parentElement.addEventListener('mouseleave', () => $el.style.color = '#94A3B8')">
                                                            View Group
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ── Students Tab ────────────────────────────────────────────────── --}}
                    @if ($tab === 'students')
                        <livewire:instructor::my-classes.students :section="$section" />
                    @endif

                    {{-- ── Schedule Tab ────────────────────────────────────────────────── --}}
                    @if ($tab === 'schedule')
                        <livewire:instructor::my-classes.schedule :section="$section" />
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 rounded-2xl border overflow-hidden bg-white"
                    style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    {{-- Gradient stripe --}}
                    <div class="h-[3px] w-full" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

                    <div class="px-5 pt-4 pb-2">
                        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1"
                            style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.05)">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                            <span
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">Overview</span>
                        </div>
                    </div>

                    <div class="px-5 pb-2 divide-y" style="divide-color: #F1F5F9">
                        <div class="py-4">
                            <p class="mb-1.5"
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Semester Details</p>
                            <p class="font-semibold" style="font-size: 0.95rem; color: #0F172A; line-height: 1.4">
                                {{ $section->semester->name ?? 'Not set' }}</p>
                            <p class="text-xs mt-1" style="color: #64748B">
                                {{ optional($section->semester?->start_date)->format('M d, Y') ?? 'TBD' }} -
                                {{ optional($section->semester?->end_date)->format('M d, Y') ?? 'TBD' }}
                            </p>
                        </div>
                        <div class="py-4">
                            <p class="mb-1.5"
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Total Students</p>
                            <p class="font-bold" style="font-size: 2rem; color: #0F172A; line-height: 1">
                                {{ $this->classData['students_count'] }}</p>
                        </div>
                        <div class="py-4">
                            <p class="mb-1.5"
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Research Groups</p>
                            <p class="font-bold" style="font-size: 2rem; color: #0052FF; line-height: 1">
                                {{ $this->classData['groups_count'] }}</p>
                        </div>
                        <div class="py-4">
                            <p class="mb-1.5"
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Approved Titles</p>
                            <p class="font-bold" style="font-size: 2rem; color: #059669; line-height: 1">6</p>
                        </div>
                        <div class="py-4">
                            <p class="mb-1.5"
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Pending Review</p>
                            <p class="font-bold" style="font-size: 2rem; color: #B45309; line-height: 1">2</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
