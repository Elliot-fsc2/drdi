<?php

use App\Models\Group;
use App\Models\Section;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
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
        if (! $this->fee) {
            return 0;
        }

        return $this->fee->total_merger_amount;
    }
};
?>

<div class="p-3 md:p-4">
  <div class="mb-6">
    <h3 class="font-semibold text-slate-900 mb-4">Fee Summary</h3>

    @if($this->fee)
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:max-w-4xl">
        <!-- Breakdown Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Fee Breakdown
                </h3>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Base Fee</p>
                        <p class="text-xs text-gray-500 mt-0.5">Standard group assessment</p>
                    </div>
                    <p class="font-semibold text-gray-900">₱{{ number_format($this->baseFee, 2) }}</p>
                </div>

                <div class="flex justify-between items-center pb-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Honorarium Total</p>
                        <p class="text-xs text-gray-500 mt-0.5">Adviser and panelist fees</p>
                    </div>
                    <p class="font-semibold text-gray-900">₱{{ number_format($this->honorariumTotal, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 flex flex-col justify-center text-center">
            <div class="text-sm text-blue-600 mb-2 uppercase tracking-wide font-semibold">Total Collectibles</div>
            <div class="text-3xl md:text-5xl font-bold text-blue-700">
                ₱{{ number_format($this->totalCollectibles, 2) }}
            </div>
            <div class="mt-4 text-sm text-blue-600/80">
                Total group fee assessment.
            </div>
        </div>
      </div>
    @else
      <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500 lg:max-w-4xl">
          <p>No fee records have been assessed for this group yet.</p>
      </div>
    @endif
  </div>
</div>