<?php

use App\Enums\InstructorRole;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')]
  class extends Component {

  public function mount()
  {
  }
};
?>

<div class="space-y-6">
  <!-- Welcome Header -->
  <div class="bg-white border-l-4 border-cyan-500 rounded-lg p-6 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-gray-500 mb-1">Welcome back,</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ auth()->user()->name }}</h1>
        <p class="text-gray-600">You have 3 pending reviews and 2 upcoming deadlines this week.</p>
      </div>
      <button
        class="hidden md:flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Project
      </button>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Projects</p>
          <p class="text-2xl font-bold text-gray-900">7</p>
        </div>
      </div>
      <p class="text-xs text-gray-500">3 active, 4 completed</p>
    </div>

    <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Publications</p>
          <p class="text-2xl font-bold text-gray-900">23</p>
        </div>
      </div>
      <p class="text-xs text-gray-500">5 pending review</p>
    </div>

    <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Collaborators</p>
          <p class="text-2xl font-bold text-gray-900">14</p>
        </div>
      </div>
      <p class="text-xs text-gray-500">Across 5 departments</p>
    </div>

    <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase tracking-wide">Due This Week</p>
          <p class="text-2xl font-bold text-gray-900">2</p>
        </div>
      </div>
      <p class="text-xs text-gray-500">1 overdue</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Research Projects -->
    <div class="lg:col-span-2 space-y-5">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Recent Projects</h2>
        <a href="#" class="text-sm text-blue-600 hover:underline">View all</a>
      </div>

      <div class="space-y-4">
        <!-- Project 1 -->
        <div
          class="bg-white rounded-lg border border-gray-200 p-5 hover:border-gray-300 transition-colors cursor-pointer">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-gray-900">Cybersecurity Threat Detection Framework</h3>
                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">Active</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">Development of ML-based intrusion detection system for
                military networks</p>
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Due Feb 28, 2026
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  3 members
                </span>
                <span>Progress: 67%</span>
              </div>
            </div>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-blue-600 h-1.5 rounded-full" style="width: 67%"></div>
          </div>
        </div>

        <!-- Project 2 -->
        <div
          class="bg-white rounded-lg border border-gray-200 p-5 hover:border-gray-300 transition-colors cursor-pointer">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-gray-900">Drone Navigation Systems Research</h3>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">On Track</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">Autonomous navigation in GPS-denied environments</p>
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Due Mar 15, 2026
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  5 members
                </span>
                <span>Progress: 43%</span>
              </div>
            </div>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-green-600 h-1.5 rounded-full" style="width: 43%"></div>
          </div>
        </div>

        <!-- Project 3 -->
        <div
          class="bg-white rounded-lg border border-orange-200 p-5 hover:border-orange-300 transition-colors cursor-pointer">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-gray-900">Materials Science - Composite Armor</h3>
                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded">Delayed</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">Testing phase postponed pending equipment calibration
              </p>
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Due Feb 15, 2026
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  2 members
                </span>
                <span>Progress: 28%</span>
              </div>
            </div>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-orange-600 h-1.5 rounded-full" style="width: 28%"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
      <!-- Upcoming Deadlines -->
      <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Upcoming Deadlines</h3>
        <div class="space-y-3">
          <div class="flex gap-3">
            <div class="flex-shrink-0 w-12 text-center">
              <div class="text-xs text-gray-500">Feb</div>
              <div class="text-lg font-bold text-gray-900">15</div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">Armor Testing Report</p>
              <p class="text-xs text-red-600">Overdue by 2 days</p>
            </div>
          </div>
          <div class="flex gap-3">
            <div class="flex-shrink-0 w-12 text-center">
              <div class="text-xs text-gray-500">Feb</div>
              <div class="text-lg font-bold text-gray-900">20</div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">Review Draft Paper #47</p>
              <p class="text-xs text-gray-500">In 7 days</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Recent Activity</h3>
        <div class="space-y-3 text-sm">
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">J. Rodriguez commented on "Detection Framework"</p>
              <p class="text-xs text-gray-500">23 minutes ago</p>
            </div>
          </div>
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">Milestone completed: Phase 2 Testing</p>
              <p class="text-xs text-gray-500">2 hours ago</p>
            </div>
          </div>
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">File uploaded: calibration_data.xlsx</p>
              <p class="text-xs text-gray-500">Yesterday</p>
            </div>
          </div>
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">Meeting notes shared by S. Kim</p>
              <p class="text-xs text-gray-500">2 days ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
