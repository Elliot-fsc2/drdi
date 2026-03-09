<?php

use App\Enums\InstructorRole;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')] class extends Component {
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 sm:mb-10">

            <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: linear-gradient(135deg, #0052FF, #4D7CFF)">
                        <x-heroicon-o-document-text class="w-5 h-5 text-white" />
                    </div>
                    <div class="min-w-0">
                        <p
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                            Projects</p>
                        <p class="text-2xl font-bold" style="color: #0F172A">7</p>
                    </div>
                </div>
                <p class="text-xs" style="color: #94A3B8">3 active, 4 completed</p>
            </div>

            <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: #ECFDF5">
                        <x-heroicon-o-check-circle class="w-5 h-5" style="color: #059669" />
                    </div>
                    <div class="min-w-0">
                        <p
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                            Publications</p>
                        <p class="text-2xl font-bold" style="color: #0F172A">23</p>
                    </div>
                </div>
                <p class="text-xs" style="color: #94A3B8">5 pending review</p>
            </div>

            <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: #F3F0FF">
                        <x-heroicon-o-user-group class="w-5 h-5" style="color: #7C3AED" />
                    </div>
                    <div class="min-w-0">
                        <p
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                            Collaborators</p>
                        <p class="text-2xl font-bold" style="color: #0F172A">14</p>
                    </div>
                </div>
                <p class="text-xs" style="color: #94A3B8">Across 5 departments</p>
            </div>

            <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: #FFF7ED">
                        <x-heroicon-o-clock class="w-5 h-5" style="color: #EA580C" />
                    </div>
                    <div class="min-w-0">
                        <p
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                            Due This Week</p>
                        <p class="text-2xl font-bold" style="color: #0F172A">2</p>
                    </div>
                </div>
                <p class="text-xs" style="color: #94A3B8">1 overdue</p>
            </div>

        </div>

        {{-- ── Main Content ─────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Recent Projects --}}
            <div class="lg:col-span-2 space-y-5">

                <div class="flex items-center justify-between">
                    <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
                        style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                            Recent Projects
                        </span>
                    </div>
                    <a href="#"
                        class="group inline-flex items-center gap-1 text-sm font-semibold transition-colors duration-150"
                        style="color: #0052FF">
                        View all
                        <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="space-y-3">

                    {{-- Project 1 --}}
                    <div class="relative overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-lg cursor-pointer"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                            style="background: linear-gradient(to bottom, #0052FF, #4D7CFF)"></div>
                        <div class="py-5 pl-6 pr-5">
                            <div class="flex items-start justify-between mb-3 gap-3">
                                <h3 class="text-sm font-semibold leading-snug" style="color: #0F172A">
                                    Cybersecurity Threat Detection Framework</h3>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium"
                                    style="background: rgba(0,82,255,0.07); color: #0052FF; border: 1px solid rgba(0,82,255,0.12)">
                                    Active
                                </span>
                            </div>
                            <p class="text-xs mb-4" style="color: #64748B; line-height: 1.5">
                                Development of ML-based intrusion detection system for military networks</p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs mb-4"
                                style="color: #94A3B8">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" /> Due Feb 28, 2026
                                </span>
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-users class="w-3.5 h-3.5" /> 3 members
                                </span>
                                <span>67% complete</span>
                            </div>
                            <div class="h-1.5 rounded-full overflow-hidden" style="background: #F1F5F9">
                                <div class="h-full rounded-full"
                                    style="width: 67%; background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Project 2 --}}
                    <div class="relative overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-lg cursor-pointer"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                            style="background: linear-gradient(to bottom, #059669, #34D399)"></div>
                        <div class="py-5 pl-6 pr-5">
                            <div class="flex items-start justify-between mb-3 gap-3">
                                <h3 class="text-sm font-semibold leading-snug" style="color: #0F172A">
                                    Drone Navigation Systems Research</h3>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium"
                                    style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0">
                                    On Track
                                </span>
                            </div>
                            <p class="text-xs mb-4" style="color: #64748B; line-height: 1.5">
                                Autonomous navigation in GPS-denied environments</p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs mb-4"
                                style="color: #94A3B8">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" /> Due Mar 15, 2026
                                </span>
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-users class="w-3.5 h-3.5" /> 5 members
                                </span>
                                <span>43% complete</span>
                            </div>
                            <div class="h-1.5 rounded-full overflow-hidden" style="background: #F1F5F9">
                                <div class="h-full rounded-full"
                                    style="width: 43%; background: linear-gradient(to right, #059669, #34D399)"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Project 3 --}}
                    <div class="relative overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-lg cursor-pointer"
                        style="border-color: #FED7AA; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                            style="background: linear-gradient(to bottom, #EA580C, #FB923C)"></div>
                        <div class="py-5 pl-6 pr-5">
                            <div class="flex items-start justify-between mb-3 gap-3">
                                <h3 class="text-sm font-semibold leading-snug" style="color: #0F172A">
                                    Materials Science — Composite Armor</h3>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium"
                                    style="background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA">
                                    Delayed
                                </span>
                            </div>
                            <p class="text-xs mb-4" style="color: #64748B; line-height: 1.5">
                                Testing phase postponed pending equipment calibration</p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs mb-4"
                                style="color: #94A3B8">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" /> Due Feb 15, 2026
                                </span>
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-users class="w-3.5 h-3.5" /> 2 members
                                </span>
                                <span>28% complete</span>
                            </div>
                            <div class="h-1.5 rounded-full overflow-hidden" style="background: #F1F5F9">
                                <div class="h-full rounded-full"
                                    style="width: 28%; background: linear-gradient(to right, #EA580C, #FB923C)"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-5">

                {{-- Upcoming Deadlines --}}
                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            Upcoming Deadlines
                        </span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex gap-3 items-start">
                            <div class="shrink-0 w-12 rounded-xl text-center py-2"
                                style="background: #FEE2E2; border: 1px solid #FECACA">
                                <div class="text-[10px] font-bold" style="color: #DC2626">FEB</div>
                                <div class="text-lg font-bold leading-none" style="color: #DC2626">15</div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold" style="color: #0F172A">Armor Testing Report</p>
                                <p class="text-xs" style="color: #DC2626">Overdue by 22 days</p>
                            </div>
                        </div>
                        <div style="border-top: 1px solid #F1F5F9"></div>
                        <div class="flex gap-3 items-start">
                            <div class="shrink-0 w-12 rounded-xl text-center py-2"
                                style="background: rgba(0,82,255,0.06); border: 1px solid rgba(0,82,255,0.12)">
                                <div class="text-[10px] font-bold" style="color: #0052FF">MAR</div>
                                <div class="text-lg font-bold leading-none" style="color: #0052FF">20</div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold" style="color: #0F172A">Review Draft Paper #47</p>
                                <p class="text-xs" style="color: #64748B">In 11 days</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            Recent Activity
                        </span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex gap-3 items-start">
                            <div class="mt-1 w-2 h-2 rounded-full shrink-0"
                                style="background: #0052FF; box-shadow: 0 0 0 3px rgba(0,82,255,0.12)"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm" style="color: #0F172A">J. Rodriguez commented on "Detection
                                    Framework"</p>
                                <p class="text-xs mt-0.5" style="color: #94A3B8">23 minutes ago</p>
                            </div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <div class="mt-1 w-2 h-2 rounded-full shrink-0"
                                style="background: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.12)"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm" style="color: #0F172A">Milestone completed: Phase 2 Testing</p>
                                <p class="text-xs mt-0.5" style="color: #94A3B8">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <div class="mt-1 w-2 h-2 rounded-full shrink-0" style="background: #CBD5E1"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm" style="color: #0F172A">File uploaded: calibration_data.xlsx</p>
                                <p class="text-xs mt-0.5" style="color: #94A3B8">Yesterday</p>
                            </div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <div class="mt-1 w-2 h-2 rounded-full shrink-0" style="background: #CBD5E1"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm" style="color: #0F172A">Meeting notes shared by S. Kim</p>
                                <p class="text-xs mt-0.5" style="color: #94A3B8">2 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
