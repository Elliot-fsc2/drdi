<?php

use Livewire\Component;
use Livewire\Attributes\Url;

new class extends Component {
  public $section;

  #[Url]
  public $tab = 'groups';

  public $classData = [
    'section' => 'BSCS-4A',
    'course' => 'Thesis 1',
    'semester' => '2nd Semester 2025-2026',
    'students_count' => 32,
    'groups_count' => 8,
  ];

  public $groups = [
    [
      'id' => 1,
      'name' => 'Group 1',
      'leader' => 'John Doe',
      'members_count' => 4,
      'title' => 'AI-Based Student Performance Prediction System',
      'status' => 'In Progress'
    ],
    [
      'id' => 2,
      'name' => 'Group 2',
      'leader' => 'Jane Smith',
      'members_count' => 4,
      'title' => 'Mobile Learning Application for Mathematics',
      'status' => 'Submitted'
    ],
    [
      'id' => 3,
      'name' => 'Group 3',
      'leader' => 'Mike Johnson',
      'members_count' => 4,
      'title' => 'IoT-Based Campus Security System',
      'status' => 'In Progress'
    ],
    [
      'id' => 4,
      'name' => 'Group 4',
      'leader' => 'Sarah Williams',
      'members_count' => 4,
      'title' => 'E-Commerce Platform with Recommendation Engine',
      'status' => 'For Review'
    ],
    [
      'id' => 5,
      'name' => 'Group 5',
      'leader' => 'David Brown',
      'members_count' => 4,
      'title' => 'Inventory Management System using RFID',
      'status' => 'In Progress'
    ],
    [
      'id' => 6,
      'name' => 'Group 6',
      'leader' => 'Emily Davis',
      'members_count' => 4,
      'title' => 'Automated Attendance System using Facial Recognition',
      'status' => 'Submitted'
    ],
    [
      'id' => 7,
      'name' => 'Group 7',
      'leader' => 'Robert Miller',
      'members_count' => 4,
      'title' => 'Online Food Ordering and Delivery System',
      'status' => 'In Progress'
    ],
    [
      'id' => 8,
      'name' => 'Group 8',
      'leader' => 'Lisa Anderson',
      'members_count' => 4,
      'title' => 'Library Management System with Analytics',
      'status' => 'Not Started'
    ],
  ];
};
?>

<x-slot name="title">
  {{ $classData['section'] }} - {{ $classData['course'] }}
</x-slot>

<div class="p-3 lg:p-3 bg-slate-50">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2 text-sm text-slate-600 mb-3">
        <a href="{{ route('instructor.classes') }}" class="hover:text-blue-600">My Classes</a>
        <span>/</span>
        <span class="text-slate-900 font-medium">{{ $classData['section'] }}</span>
      </div>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-slate-900">{{ $classData['section'] }}</h1>
          <p class="text-slate-600 mt-1">{{ $classData['course'] }} • {{ $classData['semester'] }}</p>
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
                  <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <button class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                  Add Group
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($groups as $group)
                  <div
                    class="border border-slate-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition-all cursor-pointer bg-white">
                    <div class="flex items-start justify-between mb-3">
                      <div>
                        <h3 class="font-bold text-slate-900 mb-1">{{ $group['name'] }}</h3>
                        <span class="px-2 py-0.5 text-xs font-medium rounded
                                      {{ $group['status'] === 'Submitted' ? 'bg-green-100 text-green-700' : '' }}
                                      {{ $group['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : '' }}
                                      {{ $group['status'] === 'For Review' ? 'bg-orange-100 text-orange-700' : '' }}
                                      {{ $group['status'] === 'Not Started' ? 'bg-slate-100 text-slate-700' : '' }}">
                          {{ $group['status'] }}
                        </span>
                      </div>
                    </div>

                    <p class="text-sm text-slate-700 mb-3 line-clamp-2">{{ $group['title'] }}</p>

                    <div class="flex items-center justify-between text-xs text-slate-600 pt-3 border-t border-slate-100">
                      <div class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ $group['leader'] }}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>{{ $group['members_count'] }} members</span>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <!-- Students Tab -->
          @if($tab === 'students')
            <livewire:instructor::my-classes.students />
          @endif

          <!-- Schedule Tab -->
          @if($tab === 'schedule')
            <div class="p-4">
              <p class="text-slate-600 text-center py-12">Presentation schedule will be displayed here</p>
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
              <div class="text-3xl font-bold text-slate-900">{{ $classData['students_count'] }}</div>
            </div>

            <div class="pb-4 border-b border-slate-100">
              <div class="text-xs text-slate-500 mb-1.5 uppercase tracking-wide">Research Groups</div>
              <div class="text-3xl font-bold text-slate-900">{{ $classData['groups_count'] }}</div>
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
