<?php

use App\Enums\InstructorRole;
use App\Enums\ResearchLibraryStatus;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\ResearchLibrary;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Library Submissions')] class extends Component
{
    #[Computed]
    public function eligibleGroups()
    {
        return Group::with(['section.program', 'leader', 'members', 'researchLibrary'])
            ->withCount('members')
            ->whereHas('section', fn ($q) => $q->active())
            ->whereRelation('section', 'instructor_id', auth()->user()->profileable->id)
            ->get()
            ->filter(fn (Group $group) => $group->isEligibleForLibrary())
            ->filter(fn (Group $group) => ! $group->researchLibrary || $group->researchLibrary->status === ResearchLibraryStatus::REJECTED)
            ->values()
            ->map(fn (Group $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'program' => $group->section?->program?->name ?? 'N/A',
                'section' => $group->section?->name ?? 'N/A',
                'leader' => $group->leader?->full_name ?? 'N/A',
                'members_count' => $group->members_count,
                'has_submission' => $group->researchLibrary !== null,
                'previous_note' => $group->researchLibrary?->review_note,
            ])
            ->all();
    }

    #[Computed]
    public function pendingSubmissions()
    {
        return ResearchLibrary::pending()
            ->whereHas('group.section', fn ($q) => $q->active())
            ->whereHas('group.section', fn ($q) => $q->where('instructor_id', auth()->user()->profileable->id))
            ->with(['group.section.program', 'group.leader'])
            ->latest()
            ->get()
            ->map(fn (ResearchLibrary $library) => [
                'id' => $library->id,
                'title' => $library->title,
                'group' => $library->group?->name ?? 'N/A',
                'program' => $library->group?->section?->program?->name ?? 'N/A',
                'section' => $library->group?->section?->name ?? 'N/A',
                'academic_year' => $library->academic_year,
                'submitted_at' => $library->created_at->diffForHumans(),
            ])
            ->all();
    }

    #[Computed]
    public function approvedSubmissions()
    {
        return ResearchLibrary::approved()
            ->whereHas('group.section', fn ($q) => $q->active())
            ->whereHas('group.section', fn ($q) => $q->where('instructor_id', auth()->user()->profileable->id))
            ->with(['group.section.program', 'group.leader'])
            ->latest('published_at')
            ->get()
            ->map(fn (ResearchLibrary $library) => [
                'id' => $library->id,
                'title' => $library->title,
                'group' => $library->group?->name ?? 'N/A',
                'program' => $library->group?->section?->program?->name ?? 'N/A',
                'section' => $library->group?->section?->name ?? 'N/A',
                'academic_year' => $library->academic_year,
                'published_at' => $library->published_at?->format('M d, Y') ?? 'N/A',
            ])
            ->all();
    }

    public function render()
    {
        $layout = match (true) {
            auth()->user()?->profileable_type === Instructor::class && auth()->user()?->profileable?->role === InstructorRole::RDO => 'layouts::rdo.app',
            auth()->user()?->profileable_type === Instructor::class => 'layouts::instructor.app',
            default => 'layouts::app',
        };

        return $this->view()->layout($layout)->title('Library Submissions');
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

<div class="relative min-h-screen" style="background: #F8FAFC"
    x-data="{ tab: 'eligible' }">

    {{-- Ambient background glows --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 h-[400px] w-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Page Header ────────────────────────────── --}}
        <div class="mb-8">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Research Turnover
                </span>
            </div>
            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.6rem, 3.5vw, 2.25rem); letter-spacing: -0.015em; color: #0F172A">
                Library Submissions<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                Manage research submissions for the institutional repository.
            </p>
        </div>

        {{-- ── Tabs (Alpine.js, no network requests) ─── --}}
        <div class="mb-6 flex gap-1 rounded-2xl border p-1.5"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.04)">
            <button @click="tab = 'eligible'" :class="tab === 'eligible' ? 'text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                :style="tab === 'eligible' ? 'background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 2px 8px rgba(0,82,255,0.25);' : ''"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
                Eligible ({{ count($this->eligibleGroups) }})
            </button>
            <button @click="tab = 'pending'" :class="tab === 'pending' ? 'text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                :style="tab === 'pending' ? 'background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 2px 8px rgba(0,82,255,0.25);' : ''"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
                Pending ({{ count($this->pendingSubmissions) }})
            </button>
            <button @click="tab = 'approved'" :class="tab === 'approved' ? 'text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                :style="tab === 'approved' ? 'background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 2px 8px rgba(0,82,255,0.25);' : ''"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
                Approved ({{ count($this->approvedSubmissions) }})
            </button>
        </div>

        {{-- ── Tab: Eligible ─────────────────────────── --}}
        <div x-show="tab === 'eligible'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if (empty($this->eligibleGroups))
                <div class="rounded-2xl border py-16 text-center"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <p class="mb-1 font-semibold" style="color: #0F172A">No eligible groups</p>
                    <p class="text-sm" style="color: #94A3B8">Groups must pass the Thesis B Final Defense to be eligible for library submission.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->eligibleGroups as $group)
                        <div class="overflow-hidden rounded-2xl border transition-all hover:shadow-md"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <div class="flex items-center justify-between p-5">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <h3 class="text-sm font-semibold" style="color: #0F172A">{{ $group['name'] }}</h3>
                                        @if ($group['has_submission'])
                                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium"
                                                style="border-color: #FED7AA; background: #FFF7ED; color: #C2410C">
                                                <span class="h-1 w-1 rounded-full" style="background: #EA580C"></span>
                                                Re-submit
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: #64748B">
                                        <span>{{ $group['program'] }} &bull; {{ $group['section'] }}</span>
                                        <span>Leader: {{ $group['leader'] }}</span>
                                        <span>{{ $group['members_count'] }} members</span>
                                    </div>
                                    @if ($group['previous_note'])
                                        <div class="mt-2 rounded-lg border px-3 py-2 text-xs"
                                            style="border-color: #FED7AA; background: #FFF7ED; color: #9A3412">
                                            <span class="font-semibold">Previous feedback:</span> {{ $group['previous_note'] }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('repository-requirement', $group['id']) }}" wire:navigate
                                    class="ml-4 inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                                    style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 14px rgba(0,82,255,0.3)">
                                    <x-heroicon-o-archive-box-arrow-down class="h-4 w-4" />
                                    {{ $group['has_submission'] ? 'Resubmit' : 'Submit' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Tab: Pending ──────────────────────────── --}}
        <div x-show="tab === 'pending'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if (empty($this->pendingSubmissions))
                <div class="rounded-2xl border py-16 text-center"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <p class="mb-1 font-semibold" style="color: #0F172A">No pending submissions</p>
                    <p class="text-sm" style="color: #94A3B8">Submitted research waiting for RDO review will appear here.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->pendingSubmissions as $submission)
                        <div class="overflow-hidden rounded-2xl border"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <div class="flex items-center justify-between p-5">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold mb-1.5" style="color: #0F172A">{{ $submission['title'] }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: #64748B">
                                        <span>{{ $submission['group'] }}</span>
                                        <span>{{ $submission['program'] }} &bull; {{ $submission['section'] }}</span>
                                        <span>A.Y. {{ $submission['academic_year'] }}</span>
                                    </div>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                        style="border-color: #FDE68A; background: #FEFCE8; color: #A16207">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background: #EAB308"></span>
                                        Awaiting Review
                                    </span>
                                </div>
                                <span class="ml-4 text-xs shrink-0" style="color: #94A3B8">Submitted {{ $submission['submitted_at'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Tab: Approved ─────────────────────────── --}}
        <div x-show="tab === 'approved'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if (empty($this->approvedSubmissions))
                <div class="rounded-2xl border py-16 text-center"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <p class="mb-1 font-semibold" style="color: #0F172A">No approved submissions</p>
                    <p class="text-sm" style="color: #94A3B8">Approved research will appear here once reviewed by the RDO.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->approvedSubmissions as $submission)
                        <div class="overflow-hidden rounded-2xl border"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <div class="flex items-center justify-between p-5">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold mb-1.5" style="color: #0F172A">{{ $submission['title'] }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: #64748B">
                                        <span>{{ $submission['group'] }}</span>
                                        <span>{{ $submission['program'] }} &bull; {{ $submission['section'] }}</span>
                                        <span>A.Y. {{ $submission['academic_year'] }}</span>
                                    </div>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                        style="border-color: rgba(16,185,129,0.25); background: rgba(16,185,129,0.07); color: #059669">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background: #10B981"></span>
                                        Published {{ $submission['published_at'] }}
                                    </span>
                                </div>
                                <a href="{{ route('repository') }}" wire:navigate
                                    class="ml-4 inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 hover:-translate-y-px active:scale-[0.98] border"
                                    style="border-color: #0052FF; color: #0052FF">
                                    <x-heroicon-o-eye class="h-4 w-4" />
                                    View in Repository
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
