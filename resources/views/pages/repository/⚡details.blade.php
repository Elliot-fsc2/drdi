<?php

use App\Enums\PersonnelRole;
use App\Enums\PresentationType;
use App\Models\Instructor;
use App\Models\ResearchLibrary;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Research Details')] class extends Component {
    public int $index; // holds the ResearchLibrary id

    #[Computed]
    public function library(): ResearchLibrary
    {
        return ResearchLibrary::with(['group.section.program', 'group.section.semester', 'group.leader', 'group.members', 'group.personnel.instructor', 'group.schedules'])->findOrFail($this->index);
    }

    #[Computed]
    public function adviser(): ?\App\Models\Instructor
    {
        return $this->library->group?->personnel->firstWhere('role', PersonnelRole::TECHNICAL_ADVISER)?->instructor;
    }

    #[Computed]
    public function finalDefense(): ?\App\Models\Schedule
    {
        return $this->library->group?->schedules->firstWhere('presentation_type', PresentationType::THESIS_B_FINAL);
    }

    #[Computed]
    public function panelists()
    {
        $ids = $this->finalDefense?->panelists ?? [];
        if (empty($ids)) {
            return collect();
        }

        return Instructor::whereIn('id', $ids)->orderBy('last_name')->get();
    }

    public function mount(int $index): void
    {
        $this->index = $index;
        abort_unless(ResearchLibrary::where('id', $index)->where('is_published', true)->exists(), 404);
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

    @php
        $library = $this->library;
        $group = $library->group;
        $section = $group?->section;
        $program = $section?->program;
        $leader = $group?->leader;
        $members = $group?->members ?? collect();
        $adviser = $this->adviser;
        $defense = $this->finalDefense;
        $panelists = $this->panelists;
    @endphp

    <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Back + Breadcrumb ───────────────────────── --}}
        <div class="mb-7 flex items-center gap-2" style="font-size: 12px; color: #94A3B8">
            <a href="{{ route('repository') }}" wire:navigate
                class="inline-flex items-center gap-1.5 transition-colors hover:underline" style="color: #64748B">
                Research Repository
            </a>
            <span style="color: #CBD5E1">/</span>
            <span class="truncate"
                style="color: #0052FF; font-weight: 500; max-width: 280px">{{ $library->title }}</span>
        </div>

        {{-- ── Hero Card ────────────────────────────────── --}}
        <div class="mb-6 overflow-hidden rounded-2xl border"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.06)">

            <div class="h-1 w-full" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

            <div class="p-6 sm:p-8">

                {{-- Badges row --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @if ($program)
                        <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                            style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em">
                            {{ $program->name }}
                        </span>
                    @endif
                    @if ($section)
                        <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                            style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                            {{ $section->name }}
                        </span>
                    @endif
                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                        style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                        A.Y. {{ $library->academic_year }}
                    </span>
                </div>

                {{-- Title --}}
                <h1 class="mb-4 leading-snug"
                    style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.4rem, 3vw, 2rem); letter-spacing: -0.01em; color: #0F172A">
                    {{ $library->title }}
                </h1>

                {{-- Published at --}}
                @if ($library->published_at)
                    <p class="mb-4 text-xs" style="color: #94A3B8">
                        Published {{ \Carbon\Carbon::parse($library->published_at)->format('F j, Y') }}
                    </p>
                @endif

                {{-- Meta grid --}}
                <div class="grid grid-cols-2 gap-4">
                    @if ($adviser)
                        <div>
                            <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest"
                                style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">
                                Adviser</p>
                            <p class="text-sm font-medium" style="color: #0F172A">{{ $adviser->full_name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest"
                            style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Members
                        </p>
                        <p class="text-sm font-medium" style="color: #0F172A">
                            {{ $members->count() + ($leader ? 1 : 0) }} students</p>
                    </div>
                    @if ($defense)
                        <div>
                            <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest"
                                style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Date
                                Defended</p>
                            <p class="text-sm font-medium" style="color: #0F172A">
                                {{ \Carbon\Carbon::parse($defense->date)->format('F j, Y') }}
                            </p>
                        </div>
                        @if ($group?->final_grade)
                            <div>
                                <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">
                                    Final Grade</p>
                                <p class="text-sm font-medium" style="color: #0F172A">{{ $group->final_grade }}</p>
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>

        {{-- ── Two-column layout ────────────────────────── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left: abstract + download --}}
            <div class="space-y-5 lg:col-span-2">

                {{-- Abstract --}}
                <div class="rounded-2xl border p-6"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="mb-4 flex items-center gap-3">
                        <h2 class="font-semibold" style="color: #0F172A">Abstract</h2>
                    </div>
                    <p class="text-sm leading-relaxed" style="color: #475569">{{ $library->abstract }}</p>
                </div>

                {{-- Download --}}
                <div class="rounded-2xl border p-6"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <h2 class="mb-1 font-semibold" style="color: #0F172A">Research Paper</h2>
                    <p class="mb-5 text-sm" style="color: #94A3B8">Download the full research paper in PDF format.</p>

                    @if ($library->file_path)
                        <a href="{{ asset('storage/' . $library->file_path) }}" target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-px"
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 14px rgba(0,82,255,0.3)">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            Download PDF
                        </a>
                    @else
                        <div class="rounded-xl border border-dashed px-5 py-4 text-center"
                            style="border-color: #CBD5E1; background: #F8FAFC">
                            <p class="text-sm font-medium" style="color: #94A3B8">No file uploaded yet.</p>
                            <p class="mt-0.5 text-xs" style="color: #CBD5E1">The full paper will be available once
                                uploaded.</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Right: sidebar --}}
            <div class="space-y-5">

                {{-- Group Members --}}
                <div class="rounded-2xl border p-5"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <h3 class="mb-4 text-sm font-semibold" style="color: #0F172A">Research Team</h3>

                    @if ($leader)
                        <div class="mb-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-widest"
                                style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Leader
                            </p>
                            <div class="flex items-center gap-2.5">
                                <div>
                                    <p class="text-sm font-semibold" style="color: #0F172A">
                                        {{ $leader->first_name . ' ' . $leader->last_name }}
                                    </p>
                                    <p class="text-xs" style="color: #94A3B8">Group Leader</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($members->isNotEmpty())
                        <div class="my-3 h-px" style="background: #F1F5F9"></div>
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-widest"
                                style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">
                                Members</p>
                            <div class="space-y-2.5">
                                @foreach ($members as $member)
                                    <div class="flex items-center gap-2.5">
                                        <p class="text-sm" style="color: #475569">
                                            {{ $member->first_name . ' ' . $member->last_name }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Adviser --}}
                @if ($adviser)
                    <div class="rounded-2xl border p-5"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <h3 class="mb-3 text-sm font-semibold" style="color: #0F172A">Adviser</h3>
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: #0F172A">{{ $adviser->full_name }}</p>
                                <p class="text-xs" style="color: #94A3B8">Research Adviser</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Panel Members --}}
                @if ($panelists->isNotEmpty())
                    <div class="rounded-2xl border p-5"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <h3 class="mb-3 text-sm font-semibold" style="color: #0F172A">Panel Members</h3>
                        <div class="space-y-2.5">
                            @foreach ($panelists as $panelist)
                                <div class="flex items-center gap-2.5">
                                    <p class="text-sm" style="color: #475569">{{ $panelist->full_name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Defense info --}}
                @if ($defense)
                    <div class="rounded-2xl border p-5"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <h3 class="mb-3 text-sm font-semibold" style="color: #0F172A">Final Defense</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span style="color: #94A3B8">Date</span>
                                <span class="font-medium" style="color: #0F172A">
                                    {{ \Carbon\Carbon::parse($defense->date)->format('M j, Y') }}
                                </span>
                            </div>
                            @if ($defense->venue)
                                <div class="flex justify-between">
                                    <span style="color: #94A3B8">Venue</span>
                                    <span class="font-medium text-right"
                                        style="color: #0F172A">{{ $defense->venue }}</span>
                                </div>
                            @endif
                            @if ($group?->final_grade)
                                <div class="flex justify-between border-t pt-2" style="border-color: #F1F5F9">
                                    <span style="color: #94A3B8">Final Grade</span>
                                    <span class="font-bold" style="color: #0052FF">{{ $group->final_grade }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>
