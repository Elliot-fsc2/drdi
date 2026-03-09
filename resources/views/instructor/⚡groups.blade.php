<?php

use App\Models\Group;
use App\Models\Instructor;
use App\Enums\InstructorRole;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;

new #[Title('Groups')] class extends Component {
    #[Url]
    public string $tab = 'my_groups';

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    #[Computed]
    public function groupsAssigned()
    {
        return Group::with(['section', 'members', 'leader'])
            ->withCount('members')
            ->whereRelation('personnel', 'instructor_id', auth()->user()->profileable->id)
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'section_id' => $group->section_id,
                    'title' => $group->name,
                    'leader' => $group->leader->full_name,
                    'members_count' => $group->members_count,
                ];
            });
    }
    #[Computed]
    public function groups()
    {
        return Group::with(['section', 'members', 'leader'])
            ->withCount('members')
            ->whereHas('section', fn($q) => $q->active())
            ->whereRelation('section', 'instructor_id', auth()->user()->profileable->id)
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'section_id' => $group->section_id,
                    'title' => $group->name,
                    'leader' => $group->leader->full_name,
                    'members_count' => $group->members_count,
                ];
            });
    }

    public function mount() {}
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
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

            {{-- Section label badge --}}
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Research Groups
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        My
                        <span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                            Groups
                        </span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        Manage your research groups and groups you've been assigned to advise.
                    </p>
                </div>

                {{-- Tab switcher --}}
                <div class="inline-flex items-center gap-1 rounded-xl p-1"
                    style="background: #EEF2FF; border: 1px solid rgba(0,82,255,0.12)">
                    <a href="?tab=my_groups" wire:navigate
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        style="{{ $tab === 'my_groups' ? 'background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.3)' : 'color: #64748B' }}">
                        <x-heroicon-o-rectangle-group class="h-4 w-4" />
                        My Groups
                        @if (count($this->groups) > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-xs font-bold"
                                style="{{ $tab === 'my_groups' ? 'background: rgba(255,255,255,0.25); color: white' : 'background: rgba(0,82,255,0.1); color: #0052FF' }}">
                                {{ count($this->groups) }}
                            </span>
                        @endif
                    </a>
                    <a href="?tab=assigned_groups" wire:navigate
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        style="{{ $tab === 'assigned_groups' ? 'background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.3)' : 'color: #64748B' }}">
                        <x-heroicon-o-user-group class="h-4 w-4" />
                        Assigned
                        @if (count($this->groupsAssigned) > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-xs font-bold"
                                style="{{ $tab === 'assigned_groups' ? 'background: rgba(255,255,255,0.25); color: white' : 'background: rgba(0,82,255,0.1); color: #0052FF' }}">
                                {{ count($this->groupsAssigned) }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Content ─────────────────────────────────────────────────────────────── --}}
        @php
            $activeGroups = $tab === 'my_groups' ? $this->groups : $this->groupsAssigned;
        @endphp

        @if (count($activeGroups) === 0)
            {{-- ── Empty State ────────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border flex flex-col items-center justify-center py-20 px-8 text-center"
                style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6"
                    style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 8px 24px rgba(0,82,255,0.3)">
                    <x-heroicon-o-user-group class="h-10 w-10 text-white" />
                </div>
                <h3 class="mb-2" style="font-family: 'Calistoga', Georgia, serif; font-size: 1.5rem; color: #0F172A">
                    No groups yet
                </h3>
                <p class="text-sm max-w-xs" style="color: #64748B; line-height: 1.6">
                    @if ($tab === 'my_groups')
                        Groups created under your sections will appear here.
                    @else
                        You haven't been assigned to advise any groups yet.
                    @endif
                </p>
            </div>
        @else
            {{-- ── Stats summary ───────────────────────────────────────────────────── --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mb-6 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background: #0052FF; animation: pulse 2s infinite"></span>
                    <span class="text-sm font-semibold" style="color: #0F172A">
                        {{ count($activeGroups) }} {{ count($activeGroups) === 1 ? 'group' : 'groups' }}
                    </span>
                </div>
                <div class="w-px h-4 hidden sm:block" style="background: #E2E8F0"></div>
                <span class="text-sm" style="color: #64748B">
                    {{ collect($activeGroups)->sum('members_count') }} total members
                </span>
            </div>

            {{-- ── Groups Grid ─────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($activeGroups as $group)
                    @php
                        $href =
                            $tab === 'my_groups'
                                ? route($this->routePrefix . '.classes.group.view', [
                                    'section' => $group['section_id'],
                                    'group' => $group['id'],
                                ])
                                : route($this->routePrefix . '.groups.assigned.view', $group['id']);
                    @endphp

                    <a href="{{ $href }}" wire:key="{{ $tab }}-{{ $group['id'] }}" wire:navigate
                        class="group block h-full">

                        @if ($loop->first)
                            {{-- Featured card — gradient border --}}
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
                                                {{ $tab === 'assigned_groups' ? 'Assigned' : 'Active' }}
                                            </span>
                                        </div>

                                        <h3 class="font-bold text-base leading-snug mb-1.5 transition-colors duration-200 group-hover:text-blue-600"
                                            style="color: #0F172A">
                                            {{ $group['title'] }}
                                        </h3>
                                        <p class="text-sm leading-snug" style="color: #64748B">
                                            Led by {{ $group['leader'] }}
                                        </p>
                                    </div>

                                    {{-- Stats footer --}}
                                    <div class="px-5 py-4 border-t flex items-center justify-between"
                                        style="border-color: #EFF3FF; background: #F5F8FF">
                                        <div>
                                            <div class="mb-1"
                                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                                Members
                                            </div>
                                            <div class="font-bold text-2xl" style="color: #0052FF">
                                                {{ $group['members_count'] }}
                                            </div>
                                        </div>
                                        <div class="transition-transform duration-200 group-hover:translate-x-1"
                                            style="color: #93C5FD">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
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

                                    <h3 class="font-bold text-base leading-snug mb-1.5 relative transition-colors duration-200 group-hover:text-blue-600"
                                        style="color: #0F172A">
                                        {{ $group['title'] }}
                                    </h3>
                                    <p class="text-sm leading-snug relative" style="color: #64748B">
                                        Led by {{ $group['leader'] }}
                                    </p>
                                </div>

                                {{-- Stats footer --}}
                                <div class="px-5 py-4 border-t flex items-center justify-between"
                                    style="border-color: #F1F5F9; background: #FAFAFA">
                                    <div>
                                        <div class="mb-1"
                                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                            Members
                                        </div>
                                        <div class="font-bold text-xl transition-colors duration-200 group-hover:text-blue-600"
                                            style="color: #0F172A">
                                            {{ $group['members_count'] }}
                                        </div>
                                    </div>
                                    <div class="transition-all duration-200 group-hover:translate-x-0.5"
                                        style="color: #CBD5E1" x-data x-init="$el.parentElement.parentElement.parentElement.addEventListener('mouseenter', () => $el.style.color = '#93C5FD');
                                        $el.parentElement.parentElement.parentElement.addEventListener('mouseleave', () => $el.style.color = '#CBD5E1')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
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
</div>
