<?php

use App\Models\Group;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::rdo.app')] #[Title('Group Details')] class extends Component {
    public Group $group;

    #[Url]
    public string $tab = 'details';

    #[Computed]
    public function members()
    {
        return $this->group
            ->members()
            ->select('students.id', 'students.first_name', 'students.last_name', 'students.student_number')
            ->orderByRaw('students.id = ? DESC', [$this->group->leader_id])
            ->get();
    }

    #[Computed]
    public function personnel()
    {
        return $this->group
            ->personnel()
            ->with('instructor')
            ->get();
    }

    #[Computed]
    public function schedules()
    {
        return $this->group
            ->schedules()
            ->with('section')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
    }

    #[Computed]
    public function proposals()
    {
        return $this->group
            ->proposals()
            ->with('submittedBy')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function consultations()
    {
        return $this->group
            ->consultations()
            ->with('instructor')
            ->orderByDesc('scheduled_at')
            ->get();
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

<x-slot name="title">{{ $this->group->name }} — Group Details</x-slot>

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
                <a href="{{ route('rdo.group-masterlist') }}" wire:navigate
                    class="transition-colors duration-150 hover:text-blue-500 font-medium">
                    Group Masterlist
                </a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <span style="color: #0F172A; font-weight: 600">{{ $this->group->name }}</span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-4"
                        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                            Group Details
                        </span>
                    </div>

                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        {{ $this->group->name }}
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        {{ $this->group->section->program->name }} &bull; {{ $this->group->section->name }}
                        &bull; {{ $this->group->section->instructor->first_name }}
                        {{ $this->group->section->instructor->last_name }}
                    </p>
                </div>

                {{-- Tab switcher --}}
                <div class="inline-flex items-center gap-1 rounded-xl p-1 shrink-0 flex-wrap"
                    style="background: #EEF2FF; border: 1px solid rgba(0,82,255,0.12)">
                    @foreach ([['details', 'Details'], ['schedules', 'Schedules'], ['proposals', 'Proposals'], ['consultations', 'Consultations']] as [$key, $label])
                        <a href="?tab={{ $key }}" wire:navigate
                            class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap"
                            style="{{ $tab === $key ? 'background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.3)' : 'color: #64748B' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Two-column layout ────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 lg:gap-6">

            {{-- ── Main panel ──────────────────────────────────────────────────────────────── --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                    <div class="h-[3px]" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

                    {{-- ── Details tab ─────────────────────────────────────────────────────────--}}
                    @if ($tab === 'details')
                        <div class="p-5 sm:p-6 space-y-8">

                            {{-- Final Title --}}
                            <div>
                                <h3 class="font-bold text-base mb-3" style="color: #0F172A">Final Title</h3>
                                @if ($this->group->finalTitle)
                                    <div class="p-[2px] rounded-2xl"
                                        style="background: linear-gradient(135deg, #0052FF, #4D7CFF)">
                                        <div class="bg-white rounded-[14px] p-5">
                                            <p class="font-bold leading-snug"
                                                style="font-family: 'Calistoga', Georgia, serif; font-size: 1.1rem; color: #0F172A">
                                                {{ $this->group->finalTitle->title }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm italic" style="color: #94A3B8">No finalized title yet</p>
                                @endif
                            </div>

                            {{-- Members --}}
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-bold text-base" style="color: #0F172A">Researchers</h3>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        style="background: rgba(0,82,255,0.06); color: #0052FF">
                                        {{ $this->members->count() }}
                                        {{ $this->members->count() === 1 ? 'member' : 'members' }}
                                    </span>
                                </div>
                                @if ($this->members->isEmpty())
                                    <p class="text-sm italic" style="color: #94A3B8">No members</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($this->members as $member)
                                            <div class="flex items-center gap-3 p-3 rounded-xl border"
                                                style="border-color: #F1F5F9; background: #FAFAFA">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-semibold text-sm" style="color: #0F172A">
                                                            {{ $member->first_name }} {{ $member->last_name }}
                                                        </span>
                                                        @if ($member->id === $this->group->leader_id)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                                                style="background: rgba(0,82,255,0.1); color: #0052FF; border: 1px solid rgba(0,82,255,0.2)">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                    viewBox="0 0 20 20" fill="currentColor">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                                Leader
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs mt-0.5" style="color: #94A3B8">
                                                        {{ $member->student_number }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Personnel --}}
                            <div>
                                <h3 class="font-bold text-base mb-3" style="color: #0F172A">Assigned Personnel</h3>
                                @if ($this->personnel->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach ($this->personnel as $person)
                                            <div class="flex items-center justify-between p-3 rounded-xl border"
                                                style="border-color: #F1F5F9; background: #FAFAFA">
                                                <span class="text-sm font-semibold" style="color: #0F172A">
                                                    {{ $person->instructor->first_name }} {{ $person->instructor->last_name }}
                                                </span>
                                                <span @class([
                                                    'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium',
                                                    'bg-blue-50 text-blue-700 ring-1 ring-blue-100' => $person->role->value === 'technical_adviser',
                                                    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' => $person->role->value === 'grammarian',
                                                    'bg-violet-50 text-violet-700 ring-1 ring-violet-100' => $person->role->value === 'language_critic',
                                                    'bg-amber-50 text-amber-700 ring-1 ring-amber-100' => $person->role->value === 'statistician',
                                                ])>
                                                    {{ $person->role->getLabel() }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm italic" style="color: #94A3B8">No personnel assigned</p>
                                @endif
                            </div>

                            {{-- Fee --}}
                            <div>
                                <h3 class="font-bold text-base mb-3" style="color: #0F172A">Fee Summary</h3>
                                @if ($this->group->fee)
                                    <div class="rounded-xl border p-4"
                                        style="border-color: #E2E8F0; background: #FAFAFA">
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">
                                            <div class="flex justify-between gap-2">
                                                <span style="color: #64748B">Base Fee</span>
                                                <span class="font-medium" style="color: #374151">₱{{ number_format($this->group->fee->base_fee, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between gap-2">
                                                <span style="color: #64748B">Honorarium</span>
                                                <span class="font-medium" style="color: #374151">₱{{ number_format($this->group->fee->honorarium_total, 2) }}</span>
                                            </div>
                                            @if ($this->group->fee->total_merger_amount > 0)
                                                <div class="col-span-2 flex justify-between gap-2 pt-2 mt-1"
                                                    style="border-top: 1px solid #E2E8F0">
                                                    <span class="font-semibold" style="color: #0F172A">Total</span>
                                                    <span class="font-bold"
                                                        style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                                                        ₱{{ number_format($this->group->fee->total_merger_amount, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm italic" style="color: #94A3B8">No fees recorded</p>
                                @endif
                            </div>

                        </div>
                    @endif

                    {{-- ── Schedules tab ───────────────────────────────────────────────────────--}}
                    @if ($tab === 'schedules')
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="font-bold text-base" style="color: #0F172A">Presentation History</h3>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                    style="background: rgba(0,82,255,0.06); color: #0052FF">
                                    {{ $this->schedules->count() }}
                                    {{ $this->schedules->count() === 1 ? 'schedule' : 'schedules' }}
                                </span>
                            </div>

                            @if ($this->schedules->isEmpty())
                                <div class="rounded-xl border py-16 flex flex-col items-center text-center"
                                    style="border-color: #E2E8F0">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                                        style="background: #F1F5F9">
                                        <svg class="h-7 w-7" style="color: #94A3B8" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold mb-1" style="color: #0F172A">No schedules yet</p>
                                    <p class="text-xs" style="color: #94A3B8">This group has no presentation history.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($this->schedules as $schedule)
                                        @php
                                            $statusStyle = match ($schedule->status->value) {
                                                'passed' => 'background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2)',
                                                'redefense' => 'background: rgba(245,158,11,0.08); color: #B45309; border-color: rgba(245,158,11,0.2)',
                                                'failed' => 'background: rgba(239,68,68,0.06); color: #DC2626; border-color: rgba(239,68,68,0.18)',
                                                'scheduled' => 'background: rgba(0,82,255,0.06); color: #0052FF; border-color: rgba(0,82,255,0.18)',
                                                default => 'background: #F1F5F9; color: #64748B; border-color: #E2E8F0',
                                            };
                                            $statusLabel = match ($schedule->status->value) {
                                                'passed' => 'Passed',
                                                'redefense' => 'Re-defense',
                                                'failed' => 'Failed',
                                                'scheduled' => 'Scheduled',
                                                default => $schedule->status->value,
                                            };
                                        @endphp
                                        <div class="p-5 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                            style="border-color: #F1F5F9; background: #FAFAFA">
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-3 flex-wrap mb-2">
                                                        <h4 class="font-semibold text-sm" style="color: #0F172A">
                                                            {{ $schedule->presentation_type->getLabel() }}
                                                        </h4>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                                            style="{{ $statusStyle }}">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-4 text-xs"
                                                        style="color: #94A3B8">
                                                        <span class="flex items-center gap-1.5">
                                                            <svg class="h-3.5 w-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                            {{ $schedule->date->format('M d, Y') }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5">
                                                            <svg class="h-3.5 w-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                                            –
                                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                                        </span>
                                                        @if ($schedule->venue)
                                                            <span class="flex items-center gap-1.5">
                                                                <svg class="h-3.5 w-3.5" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                </svg>
                                                                {{ $schedule->venue }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Panelists --}}
                                            @if ($schedule->panelists)
                                                <div class="pt-3 border-t" style="border-color: #E2E8F0">
                                                    <p class="text-xs font-semibold mb-2 uppercase tracking-wider"
                                                        style="color: #94A3B8">Panelists</p>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach ($schedule->panelists as $panelist)
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium"
                                                                style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0">
                                                                {{ $panelist }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ── Proposals tab ───────────────────────────────────────────────────────────--}}
                    @if ($tab === 'proposals')
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="font-bold text-base" style="color: #0F172A">Proposals</h3>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                    style="background: rgba(0,82,255,0.06); color: #0052FF">
                                    {{ $this->proposals->count() }}
                                    {{ $this->proposals->count() === 1 ? 'proposal' : 'proposals' }}
                                </span>
                            </div>

                            @if ($this->proposals->isEmpty())
                                <div class="rounded-xl border py-16 flex flex-col items-center text-center"
                                    style="border-color: #E2E8F0">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                                        style="background: #F1F5F9">
                                        <svg class="h-7 w-7" style="color: #94A3B8" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold mb-1" style="color: #0F172A">No proposals yet</p>
                                    <p class="text-xs" style="color: #94A3B8">This group hasn't submitted any proposals.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($this->proposals as $proposal)
                                        @php
                                            $proposalStatusStyle = match ($proposal->status->value) {
                                                'approved' => 'background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2)',
                                                'rejected' => 'background: rgba(239,68,68,0.06); color: #DC2626; border-color: rgba(239,68,68,0.18)',
                                                default => 'background: rgba(245,158,11,0.08); color: #B45309; border-color: rgba(245,158,11,0.2)',
                                            };
                                            $proposalStatusLabel = match ($proposal->status->value) {
                                                'approved' => 'Approved',
                                                'rejected' => 'Rejected',
                                                default => 'Pending',
                                            };
                                        @endphp
                                        <div class="p-5 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                            style="border-color: #F1F5F9; background: #FAFAFA">
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-3 flex-wrap mb-2">
                                                        <h4 class="font-semibold text-sm" style="color: #0F172A">
                                                            {{ $proposal->title }}
                                                        </h4>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                                            style="{{ $proposalStatusStyle }}">
                                                            {{ $proposalStatusLabel }}
                                                        </span>
                                                    </div>
                                                    @if ($proposal->description)
                                                        <p class="text-sm mb-2" style="color: #64748B; line-height: 1.6">
                                                            {{ $proposal->description }}
                                                        </p>
                                                    @endif
                                                    <div class="flex flex-wrap items-center gap-4 text-xs"
                                                        style="color: #94A3B8">
                                                        <span class="flex items-center gap-1.5">
                                                            <svg class="h-3.5 w-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                            {{ $proposal->submittedBy->first_name }}
                                                            {{ $proposal->submittedBy->last_name }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5">
                                                            <svg class="h-3.5 w-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ $proposal->created_at->format('M d, Y h:i A') }}
                                                        </span>
                                                        @if ($proposal->file_path)
                                                            <span class="flex items-center gap-1.5">
                                                                <svg class="h-3.5 w-3.5" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                                </svg>
                                                                <span>Attached file</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($proposal->feedback)
                                                <div class="pt-3 border-t" style="border-color: #E2E8F0">
                                                    <p class="text-xs font-semibold mb-1.5 uppercase tracking-wider"
                                                        style="color: #94A3B8">Feedback</p>
                                                    <p class="text-sm" style="color: #475569; line-height: 1.5">
                                                        {{ $proposal->feedback }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ── Consultations tab ─────────────────────────────────────────────────────--}}
                    @if ($tab === 'consultations')
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="font-bold text-base" style="color: #0F172A">Consultation History</h3>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                    style="background: rgba(0,82,255,0.06); color: #0052FF">
                                    {{ $this->consultations->count() }}
                                    {{ $this->consultations->count() === 1 ? 'consultation' : 'consultations' }}
                                </span>
                            </div>

                            @if ($this->consultations->isEmpty())
                                <div class="rounded-xl border py-16 flex flex-col items-center text-center"
                                    style="border-color: #E2E8F0">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                                        style="background: #F1F5F9">
                                        <svg class="h-7 w-7" style="color: #94A3B8" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold mb-1" style="color: #0F172A">No consultations yet</p>
                                    <p class="text-xs" style="color: #94A3B8">This group has no consultation records.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($this->consultations as $consultation)
                                        @php
                                            $consultStatusStyle = match ($consultation->status) {
                                                'completed' => 'background: rgba(16,185,129,0.08); color: #059669; border-color: rgba(16,185,129,0.2)',
                                                'cancelled' => 'background: rgba(239,68,68,0.06); color: #DC2626; border-color: rgba(239,68,68,0.18)',
                                                default => 'background: rgba(245,158,11,0.08); color: #B45309; border-color: rgba(245,158,11,0.2)',
                                            };
                                            $consultStatusLabel = match ($consultation->status) {
                                                'completed' => 'Completed',
                                                'cancelled' => 'Cancelled',
                                                default => 'Pending',
                                            };
                                        @endphp
                                        <div class="p-5 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                            style="border-color: #F1F5F9; background: #FAFAFA">
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-3 flex-wrap mb-2">
                                                        <h4 class="font-semibold text-sm" style="color: #0F172A">
                                                            {{ $consultation->instructor->first_name }}
                                                            {{ $consultation->instructor->last_name }}
                                                        </h4>
                                                        @if ($consultation->type)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium"
                                                                style="background: #EEF2FF; color: #0052FF; border: 1px solid rgba(0,82,255,0.15)">
                                                                {{ $consultation->type }}
                                                            </span>
                                                        @endif
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                                            style="{{ $consultStatusStyle }}">
                                                            {{ $consultStatusLabel }}
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-4 text-xs"
                                                        style="color: #94A3B8">
                                                        <span class="flex items-center gap-1.5">
                                                            <svg class="h-3.5 w-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                            {{ $consultation->scheduled_at->format('M d, Y') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($consultation->remarks)
                                                <div class="pt-3 border-t" style="border-color: #E2E8F0">
                                                    <p class="text-xs font-semibold mb-1.5 uppercase tracking-wider"
                                                        style="color: #94A3B8">Remarks</p>
                                                    <p class="text-sm" style="color: #475569; line-height: 1.5">
                                                        {{ $consultation->remarks }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
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
                                @if ($this->group->leader)
                                    <p class="text-sm font-semibold" style="color: #0F172A">
                                        {{ $this->group->leader->first_name }}
                                        {{ $this->group->leader->last_name }}
                                    </p>
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
                                    {{ $this->members->count() }}
                                </p>
                            </div>

                            {{-- Program --}}
                            <div class="pb-4 border-b" style="border-color: #F1F5F9">
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Program</p>
                                <p class="text-sm font-semibold" style="color: #0F172A">
                                    {{ $this->group->section->program->name }}</p>
                            </div>

                            {{-- Section --}}
                            <div class="pb-4 border-b" style="border-color: #F1F5F9">
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Section</p>
                                <p class="text-sm font-semibold" style="color: #0F172A">
                                    {{ $this->group->section->name }}</p>
                            </div>

                            {{-- Instructor --}}
                            <div class="pb-4 border-b" style="border-color: #F1F5F9">
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Instructor</p>
                                <p class="text-sm font-semibold" style="color: #0F172A">
                                    {{ $this->group->section->instructor->first_name }}
                                    {{ $this->group->section->instructor->last_name }}
                                </p>
                            </div>

                            {{-- Schedules count --}}
                            <div>
                                <p class="text-xs mb-1.5 uppercase tracking-widest"
                                    style="font-family: 'JetBrains Mono', monospace; color: #94A3B8; font-size: 10px">
                                    Presentations</p>
                                <p class="font-bold" style="font-size: 2rem; color: #0052FF; line-height: 1">
                                    {{ $this->schedules->count() }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
