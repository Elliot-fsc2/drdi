<?php

use App\Models\Group;
use App\Models\Section;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    public Section $section;

    #[Url]
    public $tab = 'groups';

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class
                 && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    #[Computed]
    public function classData(): array
    {
        return [
            'id' => $this->section->id,
            'section' => $this->section->name,
            'course' => 'Thesis 1',
            'semester' => '2nd Semester 2025-2026',
            'students_count' => 32,
            'groups_count' => 8,
        ];
    }

    #[Computed]
    public function groups()
    {
        return Group::where('section_id', $this->section->id)
            ->whereHas('section', function ($query) {
                $query->where('instructor_id', auth()->user()->profileable->id);
            })
            ->with(['leader', 'proposals'])
            ->withCount('members')
            ->get();
    }
};
?>

<x-slot name="title">
  {{ $section->name }} - {{ $section->semester->name }}
</x-slot>

<div class="p-3 lg:p-3 bg-slate-50">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2 text-sm text-slate-600 mb-3">
        <a href="{{ route($this->routePrefix . '.classes') }}" wire:navigate class="hover:text-blue-600">My Classes</a>
        <span>/</span>
        <span class="text-slate-900 font-medium">{{ $this->classData['section'] }}</span>
      </div>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-slate-900">{{ $this->classData['section'] }}</h1>
          <p class="text-slate-600 mt-1">{{ $this->classData['course'] }} • {{ $this->classData['semester'] }}</p>
        </div>

        <div class="flex gap-2">
          <button
            class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50">
            Export Data
          </button>
          <button class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
            Class Settings
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content with Sidebar Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

      <!-- Main Content Area -->
      <div class="lg:col-span-3">
        <!-- Tabs -->
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
          <div class="border-b border-slate-200 px-4">
            <div class="flex gap-6">
              <a href="?tab=groups" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $tab === 'groups' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Research Groups
              </a>
              <a href="?tab=students" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $tab === 'students' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Students
              </a>
              <a href="?tab=schedule" wire:navigate
                class="py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $tab === 'schedule' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                Schedule
              </a>
            </div>
          </div>

          <!-- Groups Tab -->
          @if($tab === 'groups')
            <div class="p-4">
              <div class="mb-4 flex items-center justify-between">
                <div class="relative flex-1 max-w-md">
                  <input type="text" placeholder="Search groups or titles..."
                    class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-md text-sm">
                  <x-heroicon-o-magnifying-glass class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                </div>
                <a href="{{ route($this->routePrefix . '.classes.group.create', ['section' => $section->id]) }}" wire:navigate
                  class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
                  <x-heroicon-o-plus class="h-4 w-4" />
                  <span>Add Group</span>
                </a>
              </div>

              @if(count($this->groups) === 0)
                <div class="text-center py-12">
                  <x-heroicon-o-user-group class="h-16 w-16 mx-auto text-slate-300 mb-4" />
                  <p class="text-slate-500 text-base font-medium mb-1">No research groups yet</p>
                  <p class="text-slate-400 text-sm">Create your first group to get started</p>
                </div>
              @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  @foreach($this->groups as $group)
                    <a href="{{ route($this->routePrefix . '.classes.group.view', ['section' => $this->classData['id'], 'group' => $group->id]) }}"
                      wire:navigate
                      class="block border border-slate-200 rounded-lg p-5 hover:border-blue-400 hover:shadow-md transition-all bg-white group">
                      <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                          <h3 class="font-semibold text-lg text-slate-900 group-hover:text-blue-600 transition-colors mb-1">
                            {{ $group->name }}
                          </h3>
                            @if($group->leader_id)
                              <p class="text-xs text-slate-500">
                                Led by {{ $group->leader->first_name }} {{ $group->leader->last_name }}
                              </p>
                            @else
                              <p class="text-xs text-slate-400 italic">No leader assigned yet</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 text-sm text-slate-500">
                          <x-heroicon-o-users class="h-4 w-4" />
                          <span>{{ $group->members_count }}</span>
                        </div>
                      </div>

                      @if($group->proposals->isNotEmpty())
                        @php
                          $latestProposal = $group->proposals->sortByDesc('created_at')->first();
                          $statusValue = $latestProposal->status instanceof \App\Enums\ProposalStatus ? $latestProposal->status->value : strtolower($latestProposal->status);
                        @endphp
                        <div class="pt-3 border-t border-slate-100">
                          <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                              <p class="text-sm font-medium text-slate-700 truncate" title="{{ $latestProposal->title }}">
                                {{ $latestProposal->title }}
                              </p>
                            </div>
                            @php
                              $statusColors = [
                                'approved' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-orange-100 text-orange-700',
                                'rejected' => 'bg-red-100 text-red-700',
                              ];
                              $statusLabels = [
                                'approved' => 'Approved',
                                'pending' => 'Pending',
                                'rejected' => 'Rejected',
                              ];
                            @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded whitespace-nowrap {{ $statusColors[$statusValue] ?? 'bg-slate-100 text-slate-600' }}">
                              {{ $statusLabels[$statusValue] ?? ucfirst($statusValue) }}
                            </span>
                          </div>
                        </div>
                      @else
                        <div class="pt-3 border-t border-slate-100">
                          <p class="text-xs text-slate-400 italic">No proposal submitted yet</p>
                        </div>
                      @endif
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          @endif

          <!-- Students Tab -->
          @if($tab === 'students')
            <livewire:instructor::my-classes.students :section="$section" />
          @endif

          <!-- Schedule Tab -->
          @if($tab === 'schedule')
            <div class="lg:p-4">
              <livewire:instructor::my-classes.schedule :section="$section" />
            </div>
          @endif
        </div>
      </div>

      <!-- Stats Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-lg p-5 sticky top-4">
          <h3 class="font-bold text-slate-900 mb-4">Class Overview</h3>

          <div class="space-y-4">
            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Total Students</div>
              <div class="text-3xl font-bold text-slate-900">{{ $this->classData['students_count'] }}</div>
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Research Groups</div>
              <div class="text-3xl font-bold text-slate-900">{{ $this->classData['groups_count'] }}</div>
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Approved Titles</div>
              <div class="text-3xl font-bold text-green-600">6</div>
            </div>

            <div>
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Pending Review</div>
              <div class="text-3xl font-bold text-orange-600">2</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
