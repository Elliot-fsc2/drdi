<?php

use App\Exports\GroupMasterlist;
use App\Models\Group;
use App\Models\Semester;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new #[Title('Group Masterlist')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $semesterId = null;

    public function mount(): void
    {
        if (!$this->semesterId) {
            $this->semesterId = Semester::active()->first()?->id;
        }
    }

    #[Computed]
    public function semesters()
    {
        return Semester::orderByDesc('start_date')->get();
    }

    #[Computed]
    public function selectedSemester()
    {
        return Semester::find($this->semesterId);
    }

    #[Computed]
    public function groups()
    {
        return Group::with(['section.program', 'section.semester', 'section.instructor', 'leader', 'members.program', 'personnel.instructor', 'fee', 'finalTitle'])
            ->whereHas('section', function ($query) {
                $query->where('semester_id', $this->semesterId);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('members', function ($memberQuery) {
                            $memberQuery
                                ->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhere('student_number', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('section.program', function ($programQuery) {
                            $programQuery->where('name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(20);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSemesterId(): void
    {
        $this->resetPage();
    }

    public function export()
    {
        $export = new GroupMasterlist($this->semesterId, $this->search);

        $timestamp = now()->format('Y-m-d_H-i-s');

        return Excel::download($export, "group-masterlist-{$timestamp}.xlsx");
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

    {{-- ── Ambient background glows ────────────────────── --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">
        {{-- ── Page Header ─────────────────────────────── --}}
        <div class="mb-8 sm:mb-10">

            {{-- Section label badge --}}
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Group Masterlist
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        Research
                        <span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                            Groups
                        </span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        {{ $this->selectedSemester?->name ?? 'No semester selected' }} — all registered research groups,
                        assigned personnel, and fee summary.
                    </p>
                </div>

                {{-- Controls --}}
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="semesterId"
                        class="h-10 rounded-xl border px-4 text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2"
                        style="border-color: #E2E8F0; color: #374151; background: white; focus:ring-color: rgba(0,82,255,0.2)">
                        @foreach ($this->semesters as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                        @endforeach
                    </select>

                    <button wire:click="export"
                        class="group inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                        style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 16px rgba(0,82,255,0.35)">
                        <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                        Export
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Search & Stats ───────────────────────────── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center mb-6">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2"
                    style="color: #94A3B8" />
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search by group name, student, or program…"
                    class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm transition-all duration-200 focus:outline-none focus:ring-2"
                    style="border-color: #E2E8F0; background: white; color: #0F172A; focus:ring-color: rgba(0,82,255,0.2)">
            </div>

            <div class="inline-flex shrink-0 items-center gap-2 rounded-xl border px-4 py-2.5"
                style="border-color: #E2E8F0; background: white">
                <span class="w-2 h-2 rounded-full" style="background: linear-gradient(135deg, #0052FF, #4D7CFF)"></span>
                <span class="text-sm font-semibold" style="color: #0F172A">{{ $this->groups->total() }}</span>
                <span class="text-sm" style="color: #64748B">{{ Str::plural('group', $this->groups->total()) }}
                    found</span>
            </div>
        </div>

        {{-- ── Content ──────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
            @if ($this->groups->isEmpty())
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <h3 class="mb-2"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: 1.5rem; color: #0F172A">
                        No groups found
                    </h3>
                    <p class="text-sm max-w-xs" style="color: #64748B; line-height: 1.6">
                        Try adjusting your search or select a different semester.
                    </p>
                </div>
            @else
                {{-- ── Mobile Card View ──────────────────── --}}
                <div class="lg:hidden divide-y" style="border-color: #F1F5F9">
                    @foreach ($this->groups as $group)
                        <div class="p-5 space-y-4 transition-colors duration-150 hover:bg-slate-50/60">

                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate font-bold text-base" style="color: #0F172A">{{ $group->name }}
                                    </h3>
                                    @if ($group->finalTitle)
                                        <p class="mt-0.5 text-xs font-medium leading-snug" style="color: #0052FF">
                                            {{ $group->finalTitle->title }}</p>
                                    @else
                                        <p class="mt-0.5 text-xs italic" style="color: #94A3B8">No finalized title</p>
                                    @endif
                                    <p class="mt-0.5 text-xs" style="color: #64748B">
                                        {{ $group->section->program->name }}</p>
                                </div>
                                @if ($group->fee)
                                    <div class="shrink-0 text-right">
                                        <p class="mb-0.5"
                                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                            Total</p>
                                        <span class="text-sm font-bold"
                                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                                            ₱{{ number_format($group->fee->total_merger_amount, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Members --}}
                            <div>
                                <p class="mb-2"
                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                                    Researchers</p>
                                <div class="space-y-1.5">
                                    @foreach ($group->members as $member)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span style="color: #374151">{{ $member->first_name }}
                                                {{ $member->last_name }}</span>
                                        </div>
                                    @endforeach
                                    @if ($group->members->isEmpty())
                                        <span class="text-sm italic" style="color: #94A3B8">No members</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Subject --}}
                            <div>
                                <p class="mb-1"
                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                                    Section</p>
                                <p class="text-sm font-medium" style="color: #374151">{{ $group->section->name }}</p>
                                <p class="text-xs" style="color: #64748B">
                                    {{ $group->section->instructor->first_name }}
                                    {{ $group->section->instructor->last_name }}</p>
                            </div>

                            {{-- Personnel --}}
                            @if ($group->personnel->isNotEmpty())
                                <div>
                                    <p class="mb-2"
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                                        Assigned Personnel</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($group->personnel as $personnel)
                                            <span @class([
                                                'inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium',
                                                'bg-blue-50 text-blue-700 ring-1 ring-blue-100' =>
                                                    $personnel->role->value === 'technical_adviser',
                                                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' =>
                                                    $personnel->role->value === 'grammarian',
                                                'bg-violet-50 text-violet-700 ring-1 ring-violet-100' =>
                                                    $personnel->role->value === 'language_critic',
                                                'bg-amber-50 text-amber-700 ring-1 ring-amber-100' =>
                                                    $personnel->role->value === 'statistician',
                                            ])>
                                                {{ $personnel->role->getLabel() }}:
                                                {{ $personnel->instructor->first_name }}
                                                {{ $personnel->instructor->last_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Fees --}}
                            @if ($group->fee)
                                <div class="rounded-xl p-3" style="background: #F8FAFC; border: 1px solid #F1F5F9">
                                    <p class="mb-2"
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                                        Fee Breakdown</p>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-sm">
                                        <div class="flex justify-between gap-2">
                                            <span style="color: #64748B">Base</span>
                                            <span class="font-medium"
                                                style="color: #374151">₱{{ number_format($group->fee->base_fee, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <span style="color: #64748B">Honorarium</span>
                                            <span class="font-medium"
                                                style="color: #374151">₱{{ number_format($group->fee->honorarium_total, 2) }}</span>
                                        </div>
                                        @if ($group->fee->total_merger_amount > 0)
                                            <div class="col-span-2 flex justify-between gap-2 pt-1.5 mt-0.5"
                                                style="border-top: 1px solid #E2E8F0">
                                                <span style="color: #64748B">Merger</span>
                                                <span class="font-semibold"
                                                    style="color: #0F172A">₱{{ number_format($group->fee->total_merger_amount, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

                {{-- ── Desktop Table View ─────────────────── --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                                <th class="px-6 py-4 text-left">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Group
                                        / Researchers</span>
                                </th>
                                <th class="px-5 py-4 text-left">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Program</span>
                                </th>
                                <th class="px-5 py-4 text-left">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Section</span>
                                </th>
                                <th class="px-5 py-4 text-left">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Personnel</span>
                                </th>
                                <th class="px-6 py-4 text-right">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Fees</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->groups as $group)
                                <tr class="transition-colors duration-150 hover:bg-[#F5F8FF]"
                                    style="border-bottom: 1px solid #F1F5F9">

                                    {{-- Group / Researchers --}}
                                    <td class="px-6 py-5 align-top">
                                        @if ($group->finalTitle)
                                            <p class="font-bold mb-2" style="color: #0052FF">
                                                {{ $group->finalTitle->title }}</p>
                                        @else
                                            <p class="font-bold mb-2" style="color: #94A3B8">No finalized
                                                title</p>
                                        @endif
                                        <div class="space-y-1.5">
                                            @foreach ($group->members as $member)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm"
                                                        style="color: #374151">{{ $member->first_name }}
                                                        {{ $member->last_name }}</span>
                                                    <span class="text-xs"
                                                        style="color: #94A3B8">({{ $member->student_number }})</span>
                                                </div>
                                            @endforeach
                                            @if ($group->members->isEmpty())
                                                <span class="text-sm italic" style="color: #94A3B8">No members</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Program --}}
                                    <td class="px-5 py-5 align-top">
                                        <span class="text-sm"
                                            style="color: #374151">{{ $group->section->program->name }}</span>
                                    </td>

                                    {{-- Subject --}}
                                    <td class="px-5 py-5 align-top">
                                        <p class="text-sm font-medium" style="color: #374151">
                                            {{ $group->section->name }}</p>
                                        <p class="mt-0.5 text-xs" style="color: #64748B">
                                            {{ $group->section->instructor->first_name }}
                                            {{ $group->section->instructor->last_name }}
                                        </p>
                                    </td>

                                    {{-- Personnel --}}
                                    <td class="px-5 py-5 align-top">
                                        @if ($group->personnel->isNotEmpty())
                                            <div class="space-y-1.5">
                                                @foreach ($group->personnel as $personnel)
                                                    <div class="flex items-center gap-2">
                                                        <span @class([
                                                            'inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium',
                                                            'bg-blue-50 text-blue-700 ring-1 ring-blue-100' =>
                                                                $personnel->role->value === 'technical_adviser',
                                                            'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' =>
                                                                $personnel->role->value === 'grammarian',
                                                            'bg-violet-50 text-violet-700 ring-1 ring-violet-100' =>
                                                                $personnel->role->value === 'language_critic',
                                                            'bg-amber-50 text-amber-700 ring-1 ring-amber-100' =>
                                                                $personnel->role->value === 'statistician',
                                                        ])>
                                                            {{ $personnel->role->getLabel() }}
                                                        </span>
                                                        <span class="text-sm" style="color: #374151">
                                                            {{ $personnel->instructor->first_name }}
                                                            {{ $personnel->instructor->last_name }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-sm italic" style="color: #94A3B8">Not assigned</span>
                                        @endif
                                    </td>

                                    {{-- Fees --}}
                                    <td class="px-6 py-5 text-right align-top">
                                        @if ($group->fee)
                                            <div class="inline-block text-right space-y-1">
                                                <div class="text-xs" style="color: #64748B">
                                                    Base: <span class="font-medium"
                                                        style="color: #374151">₱{{ number_format($group->fee->base_fee, 2) }}</span>
                                                </div>
                                                <div class="text-xs" style="color: #64748B">
                                                    Honorarium: <span class="font-medium"
                                                        style="color: #374151">₱{{ number_format($group->fee->honorarium_total, 2) }}</span>
                                                </div>
                                                <div class="pt-1 mt-1 text-sm font-bold"
                                                    style="border-top: 1px solid #F1F5F9; background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                                                    ₱{{ number_format($group->fee->total_merger_amount, 2) }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm italic" style="color: #94A3B8">No fees</span>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ── Pagination ──────────────────────────── --}}
                <div class="px-6 py-4" style="border-top: 1px solid #F1F5F9">
                    {{ $this->groups->links() }}
                </div>

            @endif
        </div>

    </div>
</div>
