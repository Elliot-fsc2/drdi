<?php

use App\Models\Group;
use App\Models\Section;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Group $group;

    public Section $section;

    public function mount()
    {
        abort_if($this->group->section->instructor_id !== auth()->user()->profileable->id, 403);
        abort_if($this->group->section_id !== $this->section->id, 403);
    }

    #[Computed]
    public function fee()
    {
        return $this->group->fee;
    }

    #[Computed]
    public function baseFee(): float
    {
        return $this->fee?->base_fee ?? 0;
    }

    #[Computed]
    public function honorariumTotal(): float
    {
        return $this->fee?->honorarium_total ?? 0;
    }

    #[Computed]
    public function totalCollectibles(): float
    {
        if (!$this->fee) {
            return 0;
        }

        return $this->fee->total_merger_amount;
    }
};
?>

<div class="p-4 md:p-5">
    @if ($this->fee)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:max-w-3xl">
            {{-- Breakdown Card --}}
            <div class="rounded-xl border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-slate-900">Fee Breakdown</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900">Base Fee</p>
                            <p class="mt-0.5 text-xs text-slate-400">Standard group assessment</p>
                        </div>
                        <p class="font-semibold text-slate-900">₱{{ number_format($this->baseFee, 2) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-900">Honorarium Total</p>
                            <p class="mt-0.5 text-xs text-slate-400">Adviser and panelist fees</p>
                        </div>
                        <p class="font-semibold text-slate-900">₱{{ number_format($this->honorariumTotal, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- Total Card --}}
            <div
                class="relative flex flex-col items-center justify-center overflow-hidden rounded-xl bg-slate-900 p-5 text-center">
                <div class="pointer-events-none absolute inset-0"
                    style="background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px); background-size: 24px 24px;">
                </div>
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full"
                    style="background: radial-gradient(circle, rgba(0,82,255,0.3) 0%, transparent 70%); filter: blur(40px);">
                </div>
                <div class="relative z-10">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.15em] text-white/40">Total Collectibles
                    </p>
                    <p class="text-4xl font-bold text-white">₱{{ number_format($this->totalCollectibles, 2) }}</p>
                    <p class="mt-3 text-xs text-white/30">Total group fee assessment</p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-10 text-center lg:max-w-3xl">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="mb-1 font-semibold text-slate-700">No fee records yet</p>
            <p class="text-sm text-slate-400">No fees have been assessed for this group yet.</p>
        </div>
    @endif
</div>
