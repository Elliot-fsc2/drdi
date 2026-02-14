<?php

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
    <div>
      <p class="text-sm text-gray-500 mb-1">Welcome back,</p>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ auth()->user()->name }}</h1>
      <p class="text-gray-600">Track your proposals, groups, and consultations all in one place.</p>
    </div>
  </div>

  <!-- My Sections & Groups -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- My Section -->
    <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        My Section
      </h3>
      <div class="p-5 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-100">
        <div class="flex justify-between items-start mb-3">
          <div>
            <p class="text-lg font-bold text-gray-900">Research Methods 101</p>
            <p class="text-sm text-gray-600">Section A</p>
          </div>
          <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Active</span>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-4">
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <div>
              <p class="text-xs text-gray-500">Instructor</p>
              <p class="font-medium">Prof. Santos</p>
            </div>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-xs text-gray-500">Schedule</p>
              <p class="font-medium">MWF 10:00 AM</p>
            </div>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <div>
              <p class="text-xs text-gray-500">Room</p>
              <p class="font-medium">Rm 301</p>
            </div>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <div>
              <p class="text-xs text-gray-500">Students</p>
              <p class="font-medium">42 enrolled</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- My Groups -->
    <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        My Groups
      </h3>
      <div class="space-y-3">
        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-100">
          <div class="flex justify-between items-start mb-2">
            <div>
              <p class="font-semibold text-gray-900">Team Alpha</p>
              <p class="text-xs text-green-700 font-medium">Group Leader</p>
            </div>
            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">4 Members</span>
          </div>
          <p class="text-sm text-gray-600 mb-3">AI-Powered Student Assessment System</p>
          <div class="flex items-center gap-2">
            <div class="flex -space-x-2">
              <div
                class="w-7 h-7 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                JD</div>
              <div
                class="w-7 h-7 rounded-full bg-purple-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                MS</div>
              <div
                class="w-7 h-7 rounded-full bg-orange-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                AR</div>
              <div
                class="w-7 h-7 rounded-full bg-pink-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                LC</div>
            </div>
          </div>
        </div>
        <div class="p-4 bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg border border-orange-100">
          <div class="flex justify-between items-start mb-2">
            <div>
              <p class="font-semibold text-gray-900">Research Squad</p>
              <p class="text-xs text-gray-600 font-medium">Member</p>
            </div>
            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">3 Members</span>
          </div>
          <p class="text-sm text-gray-600 mb-3">Mobile App for Campus Management</p>
          <div class="flex items-center gap-2">
            <div class="flex -space-x-2">
              <div
                class="w-7 h-7 rounded-full bg-cyan-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                KJ</div>
              <div
                class="w-7 h-7 rounded-full bg-teal-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                TP</div>
              <div
                class="w-7 h-7 rounded-full bg-indigo-500 text-white text-xs flex items-center justify-center border-2 border-white font-medium">
                RH</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- My Proposals -->
    <div class="lg:col-span-2 space-y-5">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">My Proposals</h2>
        <a href="#" class="text-sm text-blue-600 hover:underline">View all</a>
      </div>

      <div class="space-y-4">
        <!-- Proposal 1 - Approved -->
        <div class="bg-white rounded-lg border border-green-200 p-5 hover:border-green-300 transition-colors">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-gray-900">AI-Powered Student Assessment System</h3>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-medium">Approved</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">Development of machine learning model for automated grading and
                feedback generation to enhance student learning outcomes.</p>
              <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="text-xs">
                  <span class="text-gray-500">Group:</span>
                  <span class="font-medium text-gray-900 ml-1">Team Alpha</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Section:</span>
                  <span class="font-medium text-gray-900 ml-1">Research Methods 101-A</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Adviser:</span>
                  <span class="font-medium text-gray-900 ml-1">Prof. Santos</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Submitted:</span>
                  <span class="font-medium text-gray-900 ml-1">Jan 15, 2026</span>
                </div>
              </div>
              <div class="flex items-center gap-4 text-xs text-gray-600">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Version 2.0
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                  </svg>
                  Approved by RDO
                </span>
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-4">
            <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded transition-colors">
              View Details
            </button>
            <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded transition-colors">
              Download PDF
            </button>
          </div>
        </div>

        <!-- Proposal 2 - Under Review -->
        <div class="bg-white rounded-lg border border-yellow-200 p-5 hover:border-yellow-300 transition-colors">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-gray-900">Mobile App for Campus Management</h3>
                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded font-medium">Under Review</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">Cross-platform mobile application for student services, campus
                navigation, and real-time updates on academic activities.</p>
              <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="text-xs">
                  <span class="text-gray-500">Group:</span>
                  <span class="font-medium text-gray-900 ml-1">Research Squad</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Section:</span>
                  <span class="font-medium text-gray-900 ml-1">Advanced Programming-B</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Adviser:</span>
                  <span class="font-medium text-gray-900 ml-1">Prof. Garcia</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Submitted:</span>
                  <span class="font-medium text-gray-900 ml-1">Feb 10, 2026</span>
                </div>
              </div>
              <div class="flex items-center gap-4 text-xs text-gray-600">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Version 1.0
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Pending adviser feedback
                </span>
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-4">
            <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded transition-colors">
              View Details
            </button>
            <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded transition-colors">
              Edit Draft
            </button>
          </div>
        </div>

        <!-- Proposal 3 - Revision Needed -->
        <div class="bg-white rounded-lg border border-orange-200 p-5 hover:border-orange-300 transition-colors">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-gray-900">IoT-Based Smart Classroom System</h3>
                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded font-medium">Revision Needed</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">Smart automation system for classroom climate control and resource
                management using IoT sensors.</p>
              <div class="p-3 bg-orange-50 rounded-lg border border-orange-100 mb-3">
                <p class="text-xs text-orange-800 font-medium mb-1">Feedback from Prof. Santos:</p>
                <p class="text-xs text-orange-700">"Please expand the methodology section and include more details about
                  the IoT sensor specifications and data collection methods."</p>
              </div>
              <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="text-xs">
                  <span class="text-gray-500">Group:</span>
                  <span class="font-medium text-gray-900 ml-1">Team Alpha</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Section:</span>
                  <span class="font-medium text-gray-900 ml-1">Research Methods 101-A</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Adviser:</span>
                  <span class="font-medium text-gray-900 ml-1">Prof. Santos</span>
                </div>
                <div class="text-xs">
                  <span class="text-gray-500">Submitted:</span>
                  <span class="font-medium text-gray-900 ml-1">Jan 28, 2026</span>
                </div>
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-4">
            <button class="text-xs bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded transition-colors">
              Submit Revision
            </button>
            <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded transition-colors">
              View Feedback
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
      <!-- Consultation Details -->
      <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm flex items-center gap-2">
          <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Consultation Schedule
        </h3>
        <div class="space-y-3">
          <!-- Upcoming Consultation -->
          <div class="p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
            <div class="flex items-start gap-3 mb-2">
              <div class="flex-shrink-0 w-12 text-center">
                <div class="text-xs text-blue-600 font-medium">Feb</div>
                <div class="text-lg font-bold text-blue-700">17</div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900">Prof. Santos</p>
                <p class="text-xs text-gray-600 mb-2">2:00 PM - 3:00 PM</p>
                <p class="text-xs text-gray-700"><span class="font-medium">Topic:</span> AI Assessment System - Progress
                  Review</p>
                <p class="text-xs text-gray-700 mt-1"><span class="font-medium">Location:</span> Research Lab Room 301
                </p>
              </div>
            </div>
            <div class="flex gap-2 mt-3">
              <button class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded transition-colors">
                Join Meeting
              </button>
              <button
                class="text-xs bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-3 py-1.5 rounded transition-colors">
                Reschedule
              </button>
            </div>
          </div>

          <!-- Past Consultation -->
          <div class="p-3 border border-gray-200 rounded-lg">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 w-12 text-center opacity-60">
                <div class="text-xs text-gray-500">Feb</div>
                <div class="text-lg font-bold text-gray-700">10</div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-700">Prof. Garcia</p>
                <p class="text-xs text-gray-500 mb-1">Campus App Proposal Discussion</p>
                <div class="flex items-center gap-1 text-xs text-green-600">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd" />
                  </svg>
                  Completed
                </div>
              </div>
            </div>
          </div>

          <!-- Consultation Request -->
          <div class="p-3 border border-dashed border-gray-300 rounded-lg">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 w-12 text-center">
                <div class="text-xs text-gray-400">TBD</div>
                <div class="text-lg font-bold text-gray-400">--</div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-700">Prof. Santos</p>
                <p class="text-xs text-gray-500 mb-2">IoT Proposal Revision Review</p>
                <div class="flex items-center gap-1 text-xs text-yellow-600">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                      clip-rule="evenodd" />
                  </svg>
                  Awaiting approval
                </div>
              </div>
            </div>
          </div>
        </div>
        <button
          class="w-full mt-4 text-xs bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded transition-colors">
          Request New Consultation
        </button>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Recent Activity</h3>
        <div class="space-y-3 text-sm">
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">Group meeting notes uploaded</p>
              <p class="text-xs text-gray-500">45 minutes ago</p>
            </div>
          </div>
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">Proposal approved by adviser</p>
              <p class="text-xs text-gray-500">3 hours ago</p>
            </div>
          </div>
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-purple-500 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">New task assigned by Maria</p>
              <p class="text-xs text-gray-500">Yesterday</p>
            </div>
          </div>
          <div class="flex gap-2 items-start">
            <div class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
              <p class="text-gray-900">Resource shared in group chat</p>
              <p class="text-xs text-gray-500">2 days ago</p>
            </div>
          </div>
        </div>
      </div>

      <!-- My Classes -->
      <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">My Classes</h3>
        <div class="space-y-2">
          <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-sm font-medium text-gray-900">Research Methods</p>
            <p class="text-xs text-gray-500">Prof. Santos • MWF 10:00 AM</p>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-sm font-medium text-gray-900">Advanced Programming</p>
            <p class="text-xs text-gray-500">Prof. Garcia • TTh 2:00 PM</p>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-sm font-medium text-gray-900">Data Science</p>
            <p class="text-xs text-gray-500">Prof. Reyes • MWF 1:00 PM</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>