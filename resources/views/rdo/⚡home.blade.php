<?php

use App\Models\Group;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')]
  class extends Component
  {
      public function with(): array
      {
          return [
              'latestGroups' => Group::with(['section.program', 'section.semester', 'leader', 'members'])
                  ->latest()
                  ->take(5)
                  ->get(),
          ];
      }
  };
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="space-y-6">
  <!-- Welcome Header -->
  <div class="bg-white border-l-4 border-cyan-500 rounded-lg p-6 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-gray-500 mb-1">Welcome back,</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ auth()->user()->name }}</h1>
        <p class="text-gray-600">Research Development Office Dashboard - Monitor all research activities and groups.</p>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  @island
  <div>
    @livewire(app\Livewire\RDOStats::class)
  </div>
  @endisland

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Latest Groups -->
    <div class="lg:col-span-2 space-y-5">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Latest Research Groups</h2>
        <a href="#" class="text-sm text-blue-600 hover:underline">View all</a>
      </div>

      <div class="space-y-4">
        @forelse($latestGroups as $group)
          <div
            class="bg-white rounded-lg border border-gray-200 p-5 hover:border-gray-300 transition-colors cursor-pointer">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h3 class="font-semibold text-gray-900">{{ $group->name }}</h3>
                  @if($group->proposal)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
                      {{ $group->proposal->status }}
                    </span>
                  @else
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">No Proposal</span>
                  @endif
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  {{ $group->section->program->name }} - {{ $group->section->name }}
                </p>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Leader:
                    {{ $group->leader ? $group->leader->first_name . ' ' . $group->leader->last_name : 'None assigned' }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ $group->members->count() }} members
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ $group->section->semester->name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="bg-gray-50 rounded-lg border border-gray-200 p-8 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-gray-500">No research groups found</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>