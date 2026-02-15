<?php

use App\Models\Group;
use App\Models\GroupFee;
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
  public function totalCollectibles(): float
  {
    return $this->fee?->total_merger_amount ?? 0;
  }
};
?>

<div class="p-3 md:p-4">
  <div class="mb-6">
    <h3 class="font-semibold text-slate-900 mb-4">Fee Summary</h3>

    <div class="max-w-md">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 md:p-6">
        <div class="text-sm text-blue-600 mb-2 uppercase tracking-wide">Total Collectibles</div>
        <div class="text-3xl md:text-4xl font-bold text-blue-700">₱{{ number_format($this->totalCollectibles, 2) }}
        </div>
      </div>
    </div>
  </div>
</div>