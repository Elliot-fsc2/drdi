<?php

use App\Models\Group;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    public function with(): array
    {
        return [
            'latestGroups' => Group::with(['section.program', 'section.semester', 'leader', 'members'])
                ->latest()
                ->take(5)
                ->get(),
        ];
    }
};
?>

@assets
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
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

        {{-- ── Welcome Header ──────────────────────────── --}}
        <div class="mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    RDO Dashboard
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <p class="text-sm mb-1" style="color: #64748B">Welcome back,</p>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        {{ auth()->user()->name }}<span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        Monitor research groups, track proposals, and oversee financial activity across all departments.
                    </p>
                </div>

                <div class="flex flex-row lg:flex-col lg:items-end gap-4">
                    <div class="lg:text-right">
                        <p
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            {{ now()->format('l') }}</p>
                        <p class="mt-0.5 text-base font-semibold" style="color: #0F172A">
                            {{ now()->format('M j, Y') }}</p>
                    </div>
                    <a href="{{ route('rdo.group-masterlist') }}" wire:navigate
                        class="group inline-flex shrink-0 items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                        style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 16px rgba(0,82,255,0.35)">
                        View Masterlist
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Stats Overview ───────────────────────────── --}}
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                    Overview
                </span>
            </div>

            @island(lazy: true)
                <div>
                    @livewire(app\Livewire\RDOStats::class)
                </div>
            @endisland
        </div>

        {{-- ── Latest Research Groups ───────────────────── --}}
        <div>
            <div class="flex items-center justify-between mb-5">
                <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
                    style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                    <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                    <span
                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                        Recent Groups
                    </span>
                </div>
                <a href="{{ route('rdo.group-masterlist') }}" wire:navigate
                    class="group inline-flex items-center gap-1.5 text-sm font-semibold transition-colors duration-150"
                    style="color: #0052FF">
                    View all
                    <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($latestGroups as $group)
                    <div class="group relative cursor-pointer overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                        <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                            style="background: linear-gradient(to bottom, #0052FF, #4D7CFF)"></div>

                        <div class="py-5 pl-6 pr-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-semibold leading-snug" style="color: #0F172A">
                                            {{ $group->name }}
                                        </h3>

                                        @if ($group->proposal)
                                            @php $status = $group->proposal->status instanceof \App\Enums\ProposalStatus ? $group->proposal->status->value : strtolower((string) $group->proposal->status); @endphp

                                            @if ($status === 'approved')
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                    style="border-color: #A7F3D0; background: #ECFDF5; color: #059669">
                                                    <span class="h-1 w-1 rounded-full"
                                                        style="background: #059669"></span>
                                                    Approved
                                                </span>
                                            @elseif ($status === 'pending')
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                    style="border-color: #FDE68A; background: #FFFBEB; color: #D97706">
                                                    <span class="h-1 w-1 rounded-full"
                                                        style="background: #D97706"></span>
                                                    Pending
                                                </span>
                                            @elseif ($status === 'rejected')
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                    style="border-color: #FECACA; background: #FEF2F2; color: #DC2626">
                                                    <span class="h-1 w-1 rounded-full"
                                                        style="background: #DC2626"></span>
                                                    Rejected
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                    style="border-color: #E2E8F0; background: #F8FAFC; color: #94A3B8">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                style="border-color: #E2E8F0; background: #F8FAFC; color: #94A3B8">
                                                No Proposal
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mb-3 text-sm" style="color: #64748B">
                                        {{ $group->section->program->name }}&nbsp;&mdash;&nbsp;{{ $group->section->name }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs"
                                        style="color: #94A3B8">
                                        <span class="flex items-center gap-1.5">
                                            <x-heroicon-o-user class="h-3.5 w-3.5 shrink-0" />
                                            {{ $group->leader ? $group->leader->first_name . ' ' . $group->leader->last_name : 'No leader assigned' }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <x-heroicon-o-users class="h-3.5 w-3.5 shrink-0" />
                                            {{ $group->members->count() }}
                                            {{ Str::plural('member', $group->members->count()) }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <x-heroicon-o-calendar class="h-3.5 w-3.5 shrink-0" />
                                            {{ $group->section->semester->name }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="shrink-0 self-center opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-xl"
                                        style="background: rgba(0,82,255,0.08)">
                                        <svg class="h-4 w-4" style="color: #0052FF" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed py-20 text-center"
                        style="border-color: #E2E8F0; background: #FAFAFA">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
                            style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 8px 24px rgba(0,82,255,0.3)">
                            <x-heroicon-o-user-group class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="mb-2"
                            style="font-family: 'Calistoga', Georgia, serif; font-size: 1.4rem; color: #0F172A">
                            No research groups yet</h3>
                        <p class="text-sm max-w-xs" style="color: #64748B; line-height: 1.6">
                            Research groups will appear here once they're created.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
