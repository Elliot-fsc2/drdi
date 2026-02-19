<?php

use App\Models\Group;
use App\Models\Semester;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Group Masterlist')]
class extends Component {
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
    return Group::with([
      'section.program',
      'section.semester',
      'section.instructor',
      'leader',
      'members.program',
      'personnel.instructor',
      'fee',
    ])
      ->whereHas('section', function ($query) {
        $query->where('semester_id', $this->semesterId);
      })
      ->when($this->search, function ($query) {
        $query->where(function ($q) {
          $q->where('name', 'like', "%{$this->search}%")
            ->orWhereHas('members', function ($memberQuery) {
              $memberQuery->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('student_number', 'like', "%{$this->search}%");
            })
            ->orWhereHas('section.program', function ($programQuery) {
              $programQuery->where('name', 'like', "%{$this->search}%");
            });
        });
      })
      ->orderBy('name')
      ->paginate(10);
  }

  public function updatedSearch(): void
  {
    $this->resetPage();
  }

  public function updatedSemesterId(): void
  {
    $this->resetPage();
  }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-3 lg:p-3 bg-slate-50">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-slate-900">Group Masterlist</h1>
          <p class="text-slate-600 mt-1">All research groups • {{ $this->selectedSemester?->name ?? 'No semester selected' }}</p>
        </div>

        <div class="flex gap-2">
          <select wire:model.live="semesterId"
            class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            @foreach($this->semesters as $semester)
              <option value="{{ $semester->id }}">{{ $semester->name }}</option>
            @endforeach
          </select>
          <button class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
            Export Data
          </button>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 mb-6">
      <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="relative flex-1 max-w-md">
          <input type="text" wire:model.live.debounce.300ms="search"
            placeholder="Search by group name, student name, or program..."
            class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <x-heroicon-o-magnifying-glass class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <span class="font-medium">{{ $this->groups->total() }}</span> groups found
        </div>
      </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
      @if($this->groups->isEmpty())
        <div class="text-center py-16">
          <x-heroicon-o-user-group class="h-16 w-16 mx-auto text-slate-300 mb-4" />
          <p class="text-slate-500 text-base font-medium mb-1">No groups found</p>
          <p class="text-slate-400 text-sm">Try adjusting your search or select a different semester</p>
        </div>
      @else
        {{-- Mobile Card View --}}
        <div class="lg:hidden divide-y divide-slate-200">
          @foreach($this->groups as $group)
            <div class="p-4 space-y-4">
              {{-- Group Header --}}
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-semibold text-slate-900">{{ $group->name }}</h3>
                  <p class="text-sm text-slate-500">{{ $group->section->program->name }}</p>
                </div>
                @if($group->fee)
                  <div class="text-right">
                    <span class="text-sm font-semibold text-cyan-700">
                      ₱{{ number_format($group->fee->base_fee + $group->fee->honorarium_total + $group->fee->total_merger_amount, 2) }}
                    </span>
                    <p class="text-xs text-slate-400">Total Fees</p>
                  </div>
                @endif
              </div>

              {{-- Members --}}
              <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Researchers</p>
                <div class="space-y-1">
                  @foreach($group->members as $member)
                    <div class="flex items-center gap-2 text-sm">
                      @if($member->id === $group->leader_id)
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-cyan-100 text-cyan-700 rounded-full text-xs font-medium">L</span>
                      @else
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-slate-100 text-slate-500 rounded-full text-xs">M</span>
                      @endif
                      <span class="text-slate-700">{{ $member->first_name }} {{ $member->last_name }}</span>
                    </div>
                  @endforeach
                  @if($group->members->isEmpty())
                    <span class="text-slate-400 text-sm italic">No members</span>
                  @endif
                </div>
              </div>

              {{-- Subject & Instructor --}}
              <div class="flex flex-wrap gap-4 text-sm">
                <div>
                  <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Subject</p>
                  <p class="text-slate-700">{{ $group->section->name }}</p>
                  <p class="text-slate-500 text-xs">{{ $group->section->instructor->first_name }} {{ $group->section->instructor->last_name }}</p>
                </div>
              </div>

              {{-- Personnel --}}
              @if($group->personnel->isNotEmpty())
                <div>
                  <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Assigned Personnel</p>
                  <div class="flex flex-wrap gap-2">
                    @foreach($group->personnel as $personnel)
                      <span @class([
                        'inline-flex items-center px-2 py-1 rounded text-xs font-medium',
                        'bg-blue-100 text-blue-700' => $personnel->role->value === 'technical_adviser',
                        'bg-green-100 text-green-700' => $personnel->role->value === 'grammarian',
                        'bg-purple-100 text-purple-700' => $personnel->role->value === 'language_critic',
                        'bg-orange-100 text-orange-700' => $personnel->role->value === 'statistician',
                      ])>
                        {{ $personnel->role->getLabel() }}: {{ $personnel->instructor->first_name }} {{ $personnel->instructor->last_name }}
                      </span>
                    @endforeach
                  </div>
                </div>
              @endif

              {{-- Fees Breakdown (Mobile) --}}
              @if($group->fee)
                <div class="bg-slate-50 rounded-lg p-3">
                  <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Fee Breakdown</p>
                  <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                      <span class="text-slate-500">Base:</span>
                      <span class="font-medium text-slate-700">₱{{ number_format($group->fee->base_fee, 2) }}</span>
                    </div>
                    <div>
                      <span class="text-slate-500">Honorarium:</span>
                      <span class="font-medium text-slate-700">₱{{ number_format($group->fee->honorarium_total, 2) }}</span>
                    </div>
                    @if($group->fee->total_merger_amount > 0)
                      <div class="col-span-2">
                        <span class="text-slate-500">Merger:</span>
                        <span class="font-medium text-slate-700">₱{{ number_format($group->fee->total_merger_amount, 2) }}</span>
                      </div>
                    @endif
                  </div>
                </div>
              @endif
            </div>
          @endforeach
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Group / Researchers
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Program
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Subject
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Assigned Personnel
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Fees
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($this->groups as $group)
                <tr class="hover:bg-slate-50 transition-colors">
                  {{-- Group / Researchers --}}
                  <td class="px-6 py-4">
                    <div class="mb-2">
                      <span class="font-semibold text-slate-900">{{ $group->name }}</span>
                    </div>
                    <div class="space-y-1">
                      @foreach($group->members as $member)
                        <div class="flex items-center gap-2 text-sm">
                          @if($member->id === $group->leader_id)
                            <span class="inline-flex items-center justify-center w-5 h-5 bg-cyan-100 text-cyan-700 rounded-full text-xs font-medium" title="Leader">L</span>
                          @else
                            <span class="inline-flex items-center justify-center w-5 h-5 bg-slate-100 text-slate-500 rounded-full text-xs">M</span>
                          @endif
                          <span class="text-slate-700">{{ $member->first_name }} {{ $member->last_name }}</span>
                          <span class="text-slate-400 text-xs">({{ $member->student_number }})</span>
                        </div>
                      @endforeach
                      @if($group->members->isEmpty())
                        <span class="text-slate-400 text-sm italic">No members</span>
                      @endif
                    </div>
                  </td>

                  {{-- Class Program --}}
                  <td class="px-6 py-4">
                    <span class="text-sm text-slate-700">{{ $group->section->program->name }}</span>
                  </td>

                  {{-- Subject (Section) --}}
                  <td class="px-6 py-4">
                    <div class="text-sm">
                      <div class="font-medium text-slate-700">{{ $group->section->name }}</div>
                      <div class="text-slate-500 text-xs mt-0.5">
                        {{ $group->section->instructor->first_name }} {{ $group->section->instructor->last_name }}
                      </div>
                    </div>
                  </td>

                  {{-- Assigned Personnel --}}
                  <td class="px-6 py-4">
                    @if($group->personnel->isNotEmpty())
                      <div class="space-y-1.5">
                        @foreach($group->personnel as $personnel)
                          <div class="flex items-center gap-2">
                            <span @class([
                              'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                              'bg-blue-100 text-blue-700' => $personnel->role->value === 'technical_adviser',
                              'bg-green-100 text-green-700' => $personnel->role->value === 'grammarian',
                              'bg-purple-100 text-purple-700' => $personnel->role->value === 'language_critic',
                              'bg-orange-100 text-orange-700' => $personnel->role->value === 'statistician',
                            ])>
                              {{ $personnel->role->getLabel() }}
                            </span>
                            <span class="text-sm text-slate-600">
                              {{ $personnel->instructor->first_name }} {{ $personnel->instructor->last_name }}
                            </span>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <span class="text-slate-400 text-sm italic">Not assigned</span>
                    @endif
                  </td>

                  {{-- Fees --}}
                  <td class="px-6 py-4 text-right">
                    @if($group->fee)
                      <div class="space-y-1">
                        <div class="text-sm">
                          <span class="text-slate-500">Base:</span>
                          <span class="font-medium text-slate-700">₱{{ number_format($group->fee->base_fee, 2) }}</span>
                        </div>
                        <div class="text-sm">
                          <span class="text-slate-500">Honorarium:</span>
                          <span class="font-medium text-slate-700">₱{{ number_format($group->fee->honorarium_total, 2) }}</span>
                        </div>
                        @if($group->fee->total_merger_amount > 0)
                          <div class="text-sm">
                            <span class="text-slate-500">Merger:</span>
                            <span class="font-medium text-slate-700">₱{{ number_format($group->fee->total_merger_amount, 2) }}</span>
                          </div>
                        @endif
                        <div class="text-sm font-semibold text-cyan-700 border-t border-slate-200 pt-1 mt-1">
                          Total: ₱{{ number_format($group->fee->base_fee + $group->fee->honorarium_total + $group->fee->total_merger_amount, 2) }}
                        </div>
                      </div>
                    @else
                      <span class="text-slate-400 text-sm italic">No fees</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-200">
          {{ $this->groups->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
