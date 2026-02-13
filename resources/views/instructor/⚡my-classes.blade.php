<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('My Classes')]
  class extends Component {
  public $search = '';
  public $semester = '2nd Semester 2025-2026';

  public $classes = [
    [
      'section' => 'BSCS-4A',
      'course' => 'Thesis 1',
      'groups_count' => 8,
      'students_count' => 32,
    ],
    [
      'section' => 'BSCS-4B',
      'course' => 'Thesis 1',
      'groups_count' => 6,
      'students_count' => 24,
    ],
    [
      'section' => 'BSP-3B',
      'course' => 'Methods of Research',
      'groups_count' => 12,
      'students_count' => 48,
    ],
    [
      'section' => 'BSIT-3A',
      'course' => 'Capstone 2',
      'groups_count' => 10,
      'students_count' => 40,
    ],
  ];

  public function goToClass($section)
  {
    // Logic to redirect to the specific section workspace
  }
};
?>

<div class="p-3 lg:p-3 bg-slate-50">
  <div class="max-w-7xl mx-auto">

    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 border-b border-slate-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">My Classes</h1>
        <p class="text-slate-500 text-sm font-medium mt-1 uppercase tracking-wider">{{ $semester }}</p>
      </div>

      <div class="mt-4 md:mt-0 flex gap-3">
        <div class="relative">
          <input type="text" wire:model.live="search" placeholder="Search section..."
            class="pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-64 transition-all">
          <div class="absolute left-3 top-2.5 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    @if(count($classes) === 0)
      <div class="bg-white border p-20 text-center">
        <p class="text-slate-500">No classes assigned.</p>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($classes as $class)
          <a href="{{ route('instructor.classes.view', ['section' => $class['section']]) }}"
            wire:key="{{ $class['section'] }}" wire:navigate>
            <div
              class="bg-white border border-slate-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden group cursor-pointer">
              <div
                class="p-4 border-b border-slate-200 bg-gradient-to-r from-blue-50 via-white to-blue-50 group-hover:from-blue-100 group-hover:to-blue-50 transition-colors">
                <div class="font-bold text-slate-900 group-hover:text-blue-700 transition-colors">{{ $class['section'] }}
                </div>
                <div class="text-sm text-slate-600 mt-1">{{ $class['course'] }}</div>
              </div>

              <div class="p-4 bg-white">
                <div class="flex items-center gap-5">
                  <div class="flex-1">
                    <div class="text-xs text-slate-500 mb-1.5 font-medium uppercase tracking-wide">Students</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $class['students_count'] }}</div>
                  </div>
                  <div class="h-10 w-px bg-slate-200"></div>
                  <div class="flex-1">
                    <div class="text-xs text-slate-500 mb-1.5 font-medium uppercase tracking-wide">Groups</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $class['groups_count'] }}</div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @endif
  </div>
</div>
