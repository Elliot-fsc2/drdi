<?php

use App\Services\InstructorStatsService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')] class extends Component {
    public int $activeClasses = 0;
    public int $totalStudents = 0;
    public int $totalGroups = 0;
    public int $pendingProposals = 0;
    public $consultations;
    public $recentProposals;

    public function mount(InstructorStatsService $stats): void
    {
        $data = $stats->dashboardStats();
        $this->activeClasses = $data['active_classes'];
        $this->totalStudents = $data['total_students'];
        $this->totalGroups = $data['total_groups'];
        $this->pendingProposals = $data['proposals'];
        $this->consultations = $data['consultations'];
        $this->recentProposals = $data['recent_proposals'];
    }
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

        {{-- ── Welcome Header ──────────────────────────── --}}
        <div class="mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Instructor Portal
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
                        Manage your groups, track research progress, and review proposals.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Stats Cards ──────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-8 sm:mb-10">
            @island(defer: true)
                <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="min-w-0">
                            <p
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Active Classes</p>
                            <p class="text-2xl font-bold" style="color: #0F172A">{{ $activeClasses }}</p>
                        </div>
                    </div>
                    <p class="text-xs" style="color: #94A3B8">Currently running sections</p>
                </div>

                <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="min-w-0">
                            <p
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Total Students</p>
                            <p class="text-2xl font-bold" style="color: #0F172A">{{ $totalStudents }}</p>
                        </div>
                    </div>
                    <p class="text-xs" style="color: #94A3B8">Across all active classes</p>
                </div>

                <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="min-w-0">
                            <p
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Total Groups</p>
                            <p class="text-2xl font-bold" style="color: #0F172A">{{ $totalGroups }}</p>
                        </div>
                    </div>
                    <p class="text-xs" style="color: #94A3B8">Research groups enrolled</p>
                </div>

                <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="min-w-0">
                            <p
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                Pending Proposals</p>
                            <p class="text-2xl font-bold" style="color: #0F172A">{{ $pendingProposals }}</p>
                        </div>
                    </div>
                    <p class="text-xs" style="color: #94A3B8">Awaiting your review</p>
                </div>
            @endisland

        </div>

        {{-- ── Main Content ─────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Recent Proposals --}}
            <div class="lg:col-span-2 space-y-5">

                <div class="flex items-center justify-between">
                    <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
                        style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                            Recent Proposals
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($recentProposals as $proposal)
                        @php
                            $statusStyle = match ($proposal->status) {
                                \App\Enums\ProposalStatus::APPROVED => [
                                    'bar' => 'linear-gradient(to bottom, #059669, #34D399)',
                                    'bg' => '#ECFDF5',
                                    'color' => '#059669',
                                    'border' => '#A7F3D0',
                                ],
                                \App\Enums\ProposalStatus::REJECTED => [
                                    'bar' => 'linear-gradient(to bottom, #DC2626, #F87171)',
                                    'bg' => '#FEF2F2',
                                    'color' => '#DC2626',
                                    'border' => '#FECACA',
                                ],
                                default => [
                                    'bar' => 'linear-gradient(to bottom, #EA580C, #FB923C)',
                                    'bg' => '#FFF7ED',
                                    'color' => '#EA580C',
                                    'border' => '#FED7AA',
                                ],
                            };
                        @endphp
                        <div class="relative overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                                style="background: {{ $statusStyle['bar'] }}"></div>
                            <div class="py-5 pl-6 pr-5">
                                <div class="flex items-start justify-between mb-2 gap-3">
                                    <h3 class="text-sm font-semibold leading-snug" style="color: #0F172A">
                                        {{ $proposal->title }}</h3>
                                    <span
                                        class="shrink-0 inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium"
                                        style="background: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['color'] }}; border: 1px solid {{ $statusStyle['border'] }}">
                                        {{ ucfirst($proposal->status->value) }}
                                    </span>
                                </div>
                                @if ($proposal->description)
                                    <p class="text-xs mb-3" style="color: #64748B; line-height: 1.5">
                                        {{ Str::limit($proposal->description, 120) }}</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs" style="color: #94A3B8">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-user-group class="w-3.5 h-3.5" />
                                        {{ $proposal->group->name ?? '—' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                        {{ $proposal->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border p-10 text-center"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <x-heroicon-o-document-text class="w-8 h-8 mx-auto mb-3" style="color: #CBD5E1" />
                            <p class="text-sm" style="color: #94A3B8">No proposals yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-5">

                {{-- Consultation Schedule --}}
                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            Consultation Schedule
                        </span>
                    </div>
                    <div class="p-5 space-y-4">
                        @forelse($consultations as $consultation)
                            @unless ($loop->first)
                                <div style="border-top: 1px solid #F1F5F9"></div>
                            @endunless
                            <div class="flex gap-3 items-start">
                                <div class="shrink-0 w-12 rounded-xl text-center py-2"
                                    style="background: rgba(0,82,255,0.06); border: 1px solid rgba(0,82,255,0.12)">
                                    <div class="text-[10px] font-bold uppercase" style="color: #0052FF">
                                        {{ $consultation->scheduled_at->format('M') }}</div>
                                    <div class="text-lg font-bold leading-none" style="color: #0052FF">
                                        {{ $consultation->scheduled_at->format('d') }}</div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold" style="color: #0F172A">
                                        {{ $consultation->group->name ?? 'Unknown Group' }}</p>
                                    <p class="text-xs" style="color: #64748B">
                                        {{ $consultation->scheduled_at->format('g:i A') }}
                                        @if ($consultation->type)
                                            &middot; {{ ucfirst($consultation->type) }}
                                        @endif
                                    </p>
                                    @if ($consultation->remarks)
                                        <p class="text-xs mt-0.5 truncate" style="color: #94A3B8">
                                            {{ $consultation->remarks }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-center py-4" style="color: #94A3B8">No scheduled consultations</p>
                        @endforelse
                    </div>
                </div>



            </div>
        </div>

    </div>
</div>
