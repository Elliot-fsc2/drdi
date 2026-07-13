<?php

use App\Enums\ResearchLibraryStatus;
use App\Models\ResearchLibrary;
use App\Notifications\ApproveResearchLibrary;
use App\Notifications\RejectResearchLibrary;
use Filament\Notifications\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::rdo.app')]
#[Title('Research Approvals')]
class extends Component
{
    public ?int $declineId = null;

    public string $declineNote = '';

    #[Computed]
    public function pendingLibraries()
    {
        return ResearchLibrary::pending()
            ->whereHas('group.section', fn ($query) => $query->active())
            ->with(['group.section.program', 'group.leader', 'group.section.instructor'])
            ->latest()
            ->get()
            ->map(function (ResearchLibrary $library): array {
                return [
                    'id' => $library->id,
                    'title' => $library->title,
                    'abstract' => $library->abstract,
                    'academic_year' => $library->academic_year,
                    'file_path' => $library->file_path,
                    'group' => $library->group?->name ?? 'N/A',
                    'program' => $library->group?->section?->program?->name ?? 'N/A',
                    'section' => $library->group?->section?->name ?? 'N/A',
                    'instructor' => $library->group?->section?->instructor?->full_name ?? 'N/A',
                    'submitted_at' => $library->created_at->diffForHumans(),
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function approvedLibraries()
    {
        return ResearchLibrary::approved()
            ->whereHas('group.section', fn ($query) => $query->active())
            ->with(['group.section.program', 'group.leader', 'group.section.instructor'])
            ->latest('published_at')
            ->get()
            ->map(function (ResearchLibrary $library): array {
                return [
                    'id' => $library->id,
                    'title' => $library->title,
                    'academic_year' => $library->academic_year,
                    'group' => $library->group?->name ?? 'N/A',
                    'program' => $library->group?->section?->program?->name ?? 'N/A',
                    'section' => $library->group?->section?->name ?? 'N/A',
                    'instructor' => $library->group?->section?->instructor?->full_name ?? 'N/A',
                    'published_at' => $library->published_at?->format('M d, Y') ?? 'N/A',
                ];
            })
            ->values()
            ->all();
    }

    public function approve(int $id): void
    {
        $library = ResearchLibrary::findOrFail($id);

        $library->update([
            'status' => ResearchLibraryStatus::APPROVED,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $instructor = $library->group?->section?->instructor;
        if ($instructor?->user) {
            $instructor->user->notify(new ApproveResearchLibrary($library));
        }

        Notification::make()
            ->title('Research Approved')
            ->body('The research has been approved and is now publicly visible.')
            ->success()
            ->send();
    }

    public function confirmDecline(int $id): void
    {
        $this->declineId = $id;
        $this->declineNote = '';
    }

    public function decline(): void
    {
        $this->validate([
            'declineNote' => 'required|min:10|max:2000',
        ]);

        $library = ResearchLibrary::findOrFail($this->declineId);

        $library->update([
            'status' => ResearchLibraryStatus::REJECTED,
            'review_note' => $this->declineNote,
        ]);

        $instructor = $library->group?->section?->instructor;
        if ($instructor?->user) {
            $instructor->user->notify(new RejectResearchLibrary($library));
        }

        $this->declineId = null;
        $this->declineNote = '';

        Notification::make()
            ->title('Research Declined')
            ->body('The research has been declined with feedback.')
            ->warning()
            ->send();
    }

    public function cancelDecline(): void
    {
        $this->declineId = null;
        $this->declineNote = '';
    }

    public function render()
    {
        return $this->view()->title('Research Approvals');
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
    x-data="{
        tab: 'pending',
        declineId: @entangle('declineId'),
        declineNote: @entangle('declineNote'),
    }">

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
                    Research Approvals
                </span>
            </div>
            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.6rem, 3.5vw, 2.25rem); letter-spacing: -0.015em; color: #0F172A">
                Research Approvals<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                Review and approve research submissions before they are published to the public repository.
            </p>
        </div>

        {{-- ── Tabs (Alpine.js, no network requests) ─── --}}
        <div class="mb-6 flex gap-1 rounded-2xl border p-1.5"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.04)">
            <button @click="tab = 'pending'"
                :class="tab === 'pending' ? 'text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                :style="tab === 'pending' ? 'background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 2px 8px rgba(0,82,255,0.25);' : ''"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
                Pending ({{ count($this->pendingLibraries) }})
            </button>
            <button @click="tab = 'approved'"
                :class="tab === 'approved' ? 'text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                :style="tab === 'approved' ? 'background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 2px 8px rgba(0,82,255,0.25);' : ''"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
                Approved ({{ count($this->approvedLibraries) }})
            </button>
        </div>

        {{-- ── Tab: Pending ───────────────────────────── --}}
        <div x-show="tab === 'pending'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if (empty($this->pendingLibraries))
                <div class="rounded-2xl border py-16 text-center"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <p class="mb-1 font-semibold" style="color: #0F172A">All caught up!</p>
                    <p class="text-sm" style="color: #94A3B8">There are no pending research submissions to review.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->pendingLibraries as $library)
                        <div class="overflow-hidden rounded-2xl border transition-all hover:shadow-md"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        {{-- Badges row --}}
                                        <div class="mb-2 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em">
                                                {{ $library['program'] }}
                                            </span>
                                            <span class="rounded-full border px-2 py-0.5 text-xs font-medium"
                                                style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                                                {{ $library['section'] }} &bull; {{ $library['academic_year'] }}
                                            </span>
                                            <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                style="border-color: #FDE68A; background: #FEFCE8; color: #A16207">
                                                <span class="h-1.5 w-1.5 rounded-full inline-block" style="background: #EAB308"></span>
                                                Pending
                                            </span>
                                        </div>

                                        {{-- Title --}}
                                        <h3 class="text-base font-semibold mb-1" style="color: #0F172A">
                                            {{ $library['title'] }}
                                        </h3>

                                        {{-- Abstract --}}
                                        <p class="text-sm mb-3 line-clamp-2" style="color: #64748B">
                                            {{ $library['abstract'] }}
                                        </p>

                                        {{-- Meta --}}
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs" style="color: #94A3B8">
                                            <span><span class="font-medium" style="color: #64748B">Group:</span> {{ $library['group'] }}</span>
                                            <span><span class="font-medium" style="color: #64748B">Instructor:</span> {{ $library['instructor'] }}</span>
                                            <span>Submitted {{ $library['submitted_at'] }}</span>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex shrink-0 flex-col gap-2">
                                        @if ($library['file_path'])
                                            <a href="{{ Storage::url($library['file_path']) }}" target="_blank"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition-all border"
                                                style="border-color: #E2E8F0; color: #64748B; background: #F8FAFC">
                                                <x-heroicon-o-document-text class="h-4 w-4" />
                                                View File
                                            </a>
                                        @endif
                                        <button wire:click="approve({{ $library['id'] }})"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                                            style="background: linear-gradient(135deg, #059669 0%, #10B981 100%); box-shadow: 0 4px 14px rgba(5,150,105,0.3)">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                            Approve
                                        </button>
                                        <button wire:click="confirmDecline({{ $library['id'] }})"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-200 border hover:-translate-y-px active:scale-[0.98]"
                                            style="border-color: #FCA5A5; color: #DC2626">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                            Decline
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Tab: Approved ──────────────────────────── --}}
        <div x-show="tab === 'approved'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if (empty($this->approvedLibraries))
                <div class="rounded-2xl border py-16 text-center"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <p class="mb-1 font-semibold" style="color: #0F172A">No approved research</p>
                    <p class="text-sm" style="color: #94A3B8">Approved research from the current active semester will appear here.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->approvedLibraries as $library)
                        <div class="overflow-hidden rounded-2xl border"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <div class="flex items-center justify-between p-5">
                                <div class="flex-1 min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                            style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em">
                                            {{ $library['program'] }}
                                        </span>
                                        <span class="rounded-full border px-2 py-0.5 text-xs font-medium"
                                            style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                                            {{ $library['section'] }} &bull; {{ $library['academic_year'] }}
                                        </span>
                                        <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                            style="border-color: rgba(16,185,129,0.25); background: rgba(16,185,129,0.07); color: #059669">
                                            <span class="h-1.5 w-1.5 rounded-full inline-block" style="background: #10B981"></span>
                                            Published {{ $library['published_at'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-semibold" style="color: #0F172A">{{ $library['title'] }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs mt-1.5" style="color: #94A3B8">
                                        <span><span class="font-medium" style="color: #64748B">Group:</span> {{ $library['group'] }}</span>
                                        <span><span class="font-medium" style="color: #64748B">Instructor:</span> {{ $library['instructor'] }}</span>
                                    </div>
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

    {{-- ── Decline Modal ────────────────────────── --}}
    <div x-show="declineId !== null" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cancelDecline()"></div>
        <div class="relative w-full max-w-lg rounded-2xl border p-6"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 25px 50px rgba(0,0,0,0.15)"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 class="text-lg font-semibold mb-1" style="color: #0F172A">Decline Research</h3>
            <p class="text-sm mb-4" style="color: #64748B">Provide feedback explaining why this research needs changes.</p>

            <form wire:submit="decline">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1.5" style="color: #374151">Feedback Note</label>
                    <textarea x-model="declineNote" name="declineNote"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 min-h-[120px]"
                        style="border-color: #E2E8F0; --tw-ring-color: rgba(0,82,255,0.15)"
                        placeholder="Explain what needs to be revised..."></textarea>
                    @error('declineNote')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" wire:click="cancelDecline"
                        class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all"
                        style="border-color: #E2E8F0; color: #64748B">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200"
                        style="background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%); box-shadow: 0 4px 14px rgba(220,38,38,0.3)">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                        Decline &amp; Notify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
