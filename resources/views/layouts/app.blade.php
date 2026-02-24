<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DRDI NCST Research Portal - Department of Research Development and Innovation">
  <meta name="theme-color" content="#0891b2">
  <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">

  <title>{{ $title ? $title . ' • DRDI NCST' : 'DRDI NCST • Research Portal' }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* Custom Tooltip Styling */
    [title] {
      position: relative;
      cursor: pointer;
    }

    [title]:hover::after {
      content: attr(title);
      position: absolute;
      left: calc(100% + 12px);
      top: 50%;
      transform: translateY(-50%);
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
      color: #fff;
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      white-space: nowrap;
      z-index: 1000;
      pointer-events: none;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
      animation: tooltipSlideIn 0.2s ease-out;
    }

    [title]:hover::before {
      content: '';
      position: absolute;
      left: calc(100% + 6px);
      top: 50%;
      transform: translateY(-50%);
      border: 6px solid transparent;
      border-right-color: #1e293b;
      z-index: 1000;
      pointer-events: none;
      animation: tooltipSlideIn 0.2s ease-out;
    }

    @keyframes tooltipSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50%) translateX(-8px);
      }

      to {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
      }
    }
  </style>

  @filamentStyles
  @livewireStyles
</head>

<body class="bg-gray-50" x-data="{
    sidebarOpen: localStorage.getItem('sidebarOpen') === 'false' ? false : true,
    mobileMenuOpen: false,
    profileDropdownOpen: false,
    managementOpen: localStorage.getItem('managementOpen') === 'true' ? true : false,
    init() {
        this.$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))
        this.$watch('managementOpen', value => localStorage.setItem('managementOpen', value))
    }
}">
  <div class="flex h-screen overflow-hidden">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0" @click="mobileMenuOpen = false"
      x-cloak
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 lg:hidden"></div>

    <!-- Mobile Sidebar -->
    <aside x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform"
      x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
      x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
      x-transition:leave-end="-translate-x-full"
      x-cloak
      class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-slate-900 to-slate-800 lg:hidden flex flex-col">
      <!-- Mobile Logo & Close Button -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-slate-700/50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10  rounded-lg flex items-center justify-center shadow-lg">
            <img src="{{ asset('images/logo.png') }}" alt="DRDI Logo" class="w-10 h-10 text-white">
          </div>
          <div>
            <h1 class="text-white font-bold text-lg">DRDI</h1>
            <p class="text-slate-400 text-xs">Research Portal</p>
          </div>
        </div>
        <button @click="mobileMenuOpen = false"
          class="p-2 rounded-lg hover:bg-slate-700/50 text-slate-400 hover:text-white transition-colors">
          <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
      </div>

      <!-- Mobile Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        @php
          $userType = auth()->user()->profileable_type;
          $isInstructor = $userType === \App\Models\Instructor::class && auth()->user()->profileable?->role !== \App\Enums\InstructorRole::RDO;
          $isStudent = $userType === \App\Models\Student::class;
          $isRDO = $userType === \App\Models\Instructor::class && auth()->user()->profileable?->role === \App\Enums\InstructorRole::RDO;
        @endphp

        @if($isInstructor)
          {{-- Instructor Navigation --}}
          <a href="{{ route('instructor.home') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('instructor.home'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('instructor.home'),
          ])>
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Dashboard</span>
          </a>
          <a href="{{ route('instructor.classes') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('instructor.classes*'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('instructor.classes*'),
          ])>
            <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">My Classes</span>
          </a>
          <a href="{{ route('instructor.groups') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('instructor.groups*'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('instructor.groups*'),
          ])>
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">My Groups</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-book-open class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Publications</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Team</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-document-text class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Documents</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-chart-bar class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Analytics</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Settings</span>
          </a>
        @elseif($isStudent)
          {{-- Student Navigation --}}
          <a href="{{ route('student.home') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.home'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.home'),
          ])>
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Dashboard</span>
          </a>
          <a href="{{ route('student.proposal-title') }}" wire:navigate @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.proposal-title'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.proposal-title'),
          ])>
            <x-heroicon-o-newspaper class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Proposals</span>
          </a>
          <a href="{{ route('student.group-detail') }}" wire:navigate @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.group-detail'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.group-detail'),
          ])>
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">My Groups</span>
          </a>
          <a href="{{ route('student.consultations') }}" wire:navigate @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.consultations'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.consultations'),
          ])>
            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Consultations</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-book-open class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Resources</span>
          </a>
          <a href="#"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Settings</span>
          </a>
        @elseif($isRDO)
          {{-- RDO Navigation --}}
          <a href="{{ route('rdo.home') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.home'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.home'),
          ])>
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">Dashboard</span>
          </a>
          <a href="{{ route('rdo.classes') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.classes*'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.classes*'),
          ])>
            <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">My Classes</span>
          </a>
          <a href="{{ route('rdo.groups') }}" @class([
            'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.groups*'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.groups*'),
          ])>
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span class="font-medium">My Groups</span>
          </a>
          {{-- Management Dropdown --}}
          <div>
            <button @click="managementOpen = !managementOpen"
              class="flex items-center justify-between w-full gap-3 px-4 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all">
              <div class="flex items-center gap-3">
                <x-heroicon-o-squares-2x2 class="w-6 h-6 flex-shrink-0" />
                <span class="font-medium">Management</span>
              </div>
              <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform"
                x-bind:class="managementOpen ? 'rotate-180' : ''" />
            </button>
            <div x-show="managementOpen" x-collapse class="mt-1 ml-6 space-y-1">
              <a href="{{ route('rdo.group-masterlist') }}" wire:navigate @class([
                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.group-masterlist*'),
                'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.group-masterlist*'),
              ])>
                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium text-sm">Group Masterlist</span>
              </a>
              <a href="{{ route('rdo.thesis-fees') }}" wire:navigate @class([
                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.thesis-fees*'),
                'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.thesis-fees*'),
              ])>
                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium text-sm">Thesis Fees</span>
              </a>
              <a href="{{ route('rdo.semester-management') }}" wire:navigate @class([
                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.semester-management*'),
                'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.semester-management*'),
              ])>
                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium text-sm">Semester Tracking</span>
              </a>
            </div>
          </div>
        @endif
      </nav>
    </aside>

    <!-- Desktop Sidebar -->
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'"
      class="hidden lg:flex lg:flex-col bg-gradient-to-b from-slate-900 to-slate-800 transition-all duration-300 ease-in-out">
      <!-- Logo -->
      <div :class="sidebarOpen ? 'justify-start' : 'justify-center'"
        class="flex items-center h-16 px-4 xl:px-6 border-b border-slate-700/50 transition-all duration-300">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="DRDI Logo" class="w-10 h-10 text-white">
          </div>
          <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90">
            <h1 class="text-white font-bold text-lg whitespace-nowrap">DRDI</h1>
            <p class="text-slate-400 text-xs whitespace-nowrap">Research Portal</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 py-6 space-y-1 overflow-y-auto" :class="sidebarOpen ? 'px-4' : 'px-3'">
        @php
          $userType = auth()->user()->profileable_type;
          $isInstructor = $userType === \App\Models\Instructor::class && auth()->user()->profileable?->role !== \App\Enums\InstructorRole::RDO;
          $isStudent = $userType === \App\Models\Student::class;
          $isRDO = $userType === \App\Models\Instructor::class && auth()->user()->profileable?->role === \App\Enums\InstructorRole::RDO;
        @endphp

        @if($isInstructor)
          {{-- Instructor Navigation --}}
          <a href="{{ route('instructor.home') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('instructor.home'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('instructor.home'),
            ])
            :title="!sidebarOpen ? 'Dashboard' : null">
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Dashboard</span>
          </a>
          <a href="{{ route('instructor.classes') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('instructor.classes*'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('instructor.classes*'),
            ])
            :title="!sidebarOpen ? 'My Classes' : null">
            <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Classes</span>
          </a>
          <a href="{{ route('instructor.groups') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('instructor.groups*'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('instructor.groups*'),
            ])
            :title="!sidebarOpen ? 'My Classes' : null">
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Groups</span>
          </a>

        @elseif($isStudent)
          {{-- Student Navigation --}}
          <a href="{{ route('student.home') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.home'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.home'),
            ]) :title="!sidebarOpen ? 'Dashboard' : null">
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Dashboard</span>
          </a>
          <a href="{{ route('student.proposal-title') }}" wire:navigate
            :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.proposal-title'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.proposal-title'),
            ]) :title="!sidebarOpen ? 'Proposals' : null">
            <x-heroicon-o-newspaper class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Proposals</span>
          </a>
          <a href="{{ route('student.group-detail') }}" wire:navigate
            :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.group-detail'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.group-detail'),
            ]) :title="!sidebarOpen ? 'My Groups' : null">
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Groups</span>
          </a>
          <a href="{{ route('student.consultations') }}" wire:navigate
            :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('student.consultations'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('student.consultations'),
            ]) :title="!sidebarOpen ? 'Consultations' : null">
            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Consultations</span>
          </a>
          <a href="#" :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            class="flex items-center gap-3 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all group"
            :title="!sidebarOpen ? 'Resources' : null">
            <x-heroicon-o-book-open class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Resources</span>
          </a>
          <a href="#" :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            class="flex items-center gap-3 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all group"
            :title="!sidebarOpen ? 'Settings' : null">
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Settings</span>
          </a>
        @elseif($isRDO)
          {{-- RDO Navigation --}}
          <a href="{{ route('rdo.home') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
            'flex items-center gap-3 py-2 rounded-xl transition-all group',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.home'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.home'),
          ]) :title="!sidebarOpen ? 'Dashboard' : null">
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Dashboard</span>
          </a>
          <a href="{{ route('rdo.classes') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'"
            @class([
              'flex items-center gap-3 py-2 rounded-xl transition-all group',
              'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.classes*'),
              'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.classes*'),
            ]) :title="!sidebarOpen ? 'My Classes' : null">
            <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Classes</span>
          </a>

          <a href="{{ route('rdo.groups') }}" wire:navigate :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
            'flex items-center gap-3 py-2 rounded-xl transition-all group',
            'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.groups*'),
            'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.groups*'),
          ]) :title="!sidebarOpen ? 'My Classes' : null">
            <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
            <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Groups</span>
          </a>

          {{-- Management Dropdown --}}
          <div>
            <button @click="!sidebarOpen ? (sidebarOpen = true, managementOpen = true) : managementOpen = !managementOpen"
              :class="sidebarOpen ? 'px-4 justify-between' : 'px-3 justify-center'"
              class="flex items-center w-full gap-3 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all group"
              :title="!sidebarOpen ? 'Management' : null">

              <div class="flex items-center gap-3">
                <x-heroicon-o-squares-2x2 class="w-6 h-6 flex-shrink-0" />
                <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Management</span>
              </div>

              <x-heroicon-o-chevron-down x-show="sidebarOpen" class="w-4 h-4 transition-transform"
                x-bind:class="managementOpen ? 'rotate-180' : ''" />
            </button>

            <div x-show="managementOpen && sidebarOpen" x-collapse class="mt-1 ml-6 space-y-1">
              <a href="{{ route('rdo.group-masterlist') }}" wire:navigate @class([
                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.group-masterlist*'),
                'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.group-masterlist*'),
              ])>
                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium text-sm whitespace-nowrap">Group Masterlist</span>
              </a>
              <a href="{{ route('rdo.thesis-fees') }}" wire:navigate @class([
                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.thesis-fees*'),
                'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.thesis-fees*'),
              ])>
                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium text-sm whitespace-nowrap">Thesis Fees</span>
              </a>
              <a href="{{ route('rdo.semester-management') }}" wire:navigate @class([
                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' => request()->routeIs('rdo.semester-management*'),
                'text-slate-400 hover:text-white hover:bg-slate-700/50' => !request()->routeIs('rdo.semester-management*'),
              ])>
                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium text-sm">Semester Tracking</span>
              </a>
            </div>
          </div>

        @endif
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Header -->
      <header class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <!-- Mobile Menu & Search -->
          <div class="flex items-center gap-4 flex-1">
            <!-- Mobile Menu Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen"
              class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
              <x-heroicon-o-bars-3 class="w-6 h-6 text-gray-600" />
            </button>

            <!-- Desktop Sidebar Toggle -->
            <button @click="sidebarOpen = !sidebarOpen"
              class="hidden lg:block p-2 rounded-lg hover:bg-gray-100 transition-colors" title="Toggle Sidebar">
              <x-heroicon-o-bars-3 class="w-6 h-6 text-gray-600" />
            </button>

            <div class="hidden md:block flex-1 max-w-md">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input type="search" placeholder="Search projects, documents..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-cyan-500 focus:border-transparent outline-none bg-gray-50">
              </div>
            </div>
          </div>

          <!-- Right Actions -->
          <div class="flex items-center gap-3">
            <!-- Notifications -->
            <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
              <x-heroicon-o-bell class="w-6 h-6 text-gray-600" />
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
              <button @click="open = !open"
                class="flex items-center gap-3 pl-3 border-l border-gray-200 hover:bg-gray-50 rounded-lg transition-colors py-1 pr-2">
                <div class="hidden md:block text-right">
                  <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                  <p class="text-xs text-gray-500">{{ auth()->user()->profileable->role ?? 'Student' }}</p>
                </div>
                <div
                  class="w-10 h-10 bg-gradient-to-br from-orange-400 to-pink-500 rounded-lg flex items-center justify-center shadow-md">
                  <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <x-heroicon-o-chevron-down class="hidden md:block w-4 h-4 text-gray-500" />
              </button>

              <!-- Dropdown Menu -->
              <div x-show="open" x-transition:enter="transition ease-out duration-100" x-cloak
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                  <!-- User Info -->
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                  </div>

                  <!-- Menu Items -->
                  <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <x-heroicon-o-user class="w-4 h-4" />
                    My Profile
                  </a>
                  <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                    Settings
                  </a>

                  <div class="border-t border-gray-100 my-1"></div>

                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                      class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                      <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                      Logout
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-2 md:p-6">
        {{ $slot }}
      </main>
    </div>
  </div>
  @livewire('notifications')
  @livewireScripts
  @filamentScripts
</body>

</html>