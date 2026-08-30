<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DRDI NCST Research Portal - Department of Research Development and Innovation">
    <meta name="theme-color" content="#0891b2">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">

    <title>{{ isset($title) ? $title . ' • DRDI NCST' : 'DRDI NCST • Research Portal' }}</title>

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
            x-transition:leave-end="opacity-0" @click="mobileMenuOpen = false" x-cloak
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 lg:hidden"></div>

        <!-- Mobile Sidebar -->
        <aside x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full" x-cloak
            class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-blue-900 to-blue-800 lg:hidden flex flex-col">
            <!-- Mobile Logo & Close Button -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-blue-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10  rounded-lg flex items-center justify-center shadow-lg">
                        <img src="{{ asset('images/logo.png') }}" alt="DRDI Logo" class="w-10 h-10 text-white">
                    </div>
                    <div>
                        <h1 class="text-white font-bold text-lg">DRDI</h1>
                        <p class="text-blue-300 text-xs">Research Portal</p>
                    </div>
                </div>
                <button @click="mobileMenuOpen = false"
                    class="p-2 rounded-lg hover:bg-blue-700/50 text-blue-200 hover:text-white transition-colors">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <!-- Mobile Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                @php
                    $userType = auth()->user()->profileable_type;
                    $userRole = auth()->user()->profileable?->role;
                    $isInstructor =
                        $userType === \App\Models\Instructor::class &&
                        $userRole !== \App\Enums\InstructorRole::RDO &&
                        $userRole !== \App\Enums\InstructorRole::Staff;
                    $isStudent = $userType === \App\Models\Student::class;
                    $isRDO =
                        $userType === \App\Models\Instructor::class &&
                        $userRole === \App\Enums\InstructorRole::RDO;
                    $isStaff =
                        $userType === \App\Models\Instructor::class &&
                        $userRole === \App\Enums\InstructorRole::Staff;
                    $showRdoNav = $isRDO || $isStaff;
                @endphp

                @if ($isInstructor)
                    {{-- Instructor Navigation --}}
                    <a href="{{ route('instructor.home') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'instructor.home'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'instructor.home'),
                    ])>
                        <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('instructor.classes') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'instructor.classes*'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'instructor.classes*'),
                    ])>
                        <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">My Classes</span>
                    </a>
                    <a href="{{ route('instructor.groups') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'instructor.groups*'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'instructor.groups*'),
                    ])>
                        <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">My Groups</span>
                    </a>
                    @if(Route::has('instructor.schedule-management'))
                    <a href="{{ route('instructor.schedule-management') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'instructor.schedule-management'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'instructor.schedule-management'),
                    ])>
                        <x-heroicon-o-calendar class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Schedules</span>
                    </a>
                    @endif
                @elseif($isStudent)
                    {{-- Student Navigation --}}
                    <a href="{{ route('student.home') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'student.home'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'student.home'),
                    ])>
                        <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('student.proposal-title') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'student.proposal-title'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'student.proposal-title'),
                    ])>
                        <x-heroicon-o-newspaper class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Proposals</span>
                    </a>
                    <a href="{{ route('student.group-detail') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'student.group-detail'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'student.group-detail'),
                    ])>
                        <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">My Groups</span>
                    </a>
                    <a href="{{ route('student.consultations') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'student.consultations'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'student.consultations'),
                    ])>
                        <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Consultations</span>
                    </a>
                @elseif($showRdoNav)
                    {{-- RDO / Staff Navigation --}}
                    <a href="{{ route('rdo.home') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'rdo.home'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'rdo.home'),
                    ])>
                        <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="{{ route('rdo.classes') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'rdo.classes*'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'rdo.classes*'),
                    ])>
                        <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">My Classes</span>
                    </a>
                    <a href="{{ route('rdo.groups') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'rdo.groups*'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'rdo.groups*'),
                    ])>
                        <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">My Groups</span>
                    </a>
                    <a href="{{ route('rdo.schedule-management') }}" wire:navigate @class([
                        'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'rdo.schedule-management'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'rdo.schedule-management'),
                    ])>
                        <x-heroicon-o-calendar class="w-6 h-6 flex-shrink-0" />
                        <span class="font-medium">Schedules</span>
                    </a>
                    {{-- Management Dropdown --}}
                    <div>
                        <button @click="managementOpen = !managementOpen"
                            class="flex items-center justify-between w-full gap-3 px-4 py-2 rounded-xl text-blue-200 hover:text-white hover:bg-blue-700/50 transition-all">
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
                                'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                    'rdo.group-masterlist*'),
                                'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                    'rdo.group-masterlist*'),
                            ])>
                                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                                <span class="font-medium text-sm">Group Masterlist</span>
                            </a>
                            <a href="{{ route('rdo.thesis-fees') }}" wire:navigate @class([
                                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                                'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                    'rdo.thesis-fees*'),
                                'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                    'rdo.thesis-fees*'),
                            ])>
                                <x-heroicon-o-currency-dollar class="w-5 h-5 flex-shrink-0" />
                                <span class="font-medium text-sm">Thesis Fees</span>
                            </a>
                            <a href="{{ route('rdo.semester-management') }}" wire:navigate
                                @class([
                                    'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                                    'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                        'rdo.semester-management*'),
                                    'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                        'rdo.semester-management*'),
                                ])>
                                <x-heroicon-o-calendar-days class="w-5 h-5 flex-shrink-0" />
                                <span class="font-medium text-sm">Semester Tracking</span>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Repository - All Roles --}}
                <a href="{{ route('repository') }}" wire:navigate @class([
                    'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                    'bg-white/15 text-white border border-white/25' => request()->routeIs(
                        'repository*'),
                    'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                        'repository*'),
                ])>
                    <x-heroicon-o-book-open class="w-6 h-6 flex-shrink-0" />
                    <span class="font-medium">Repository</span>
                </a>
            </nav>
        </aside>

        <!-- Desktop Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden lg:flex lg:flex-col bg-blue-800 transition-all duration-300 ease-in-out">
            <!-- Logo -->
            <div :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="flex items-center h-16 px-4 xl:px-6 border-b border-blue-700/50 transition-all duration-300">
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
                        <p class="text-blue-300 text-xs whitespace-nowrap">Research Portal</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-6 space-y-1 overflow-y-auto" :class="sidebarOpen ? 'px-4' : 'px-3'">
                @php
                    $userType = auth()->user()->profileable_type;
                    $userRole = auth()->user()->profileable?->role;
                    $isInstructor =
                        $userType === \App\Models\Instructor::class &&
                        $userRole !== \App\Enums\InstructorRole::RDO &&
                        $userRole !== \App\Enums\InstructorRole::Staff;
                    $isStudent = $userType === \App\Models\Student::class;
                    $isRDO =
                        $userType === \App\Models\Instructor::class &&
                        $userRole === \App\Enums\InstructorRole::RDO;
                    $isStaff =
                        $userType === \App\Models\Instructor::class &&
                        $userRole === \App\Enums\InstructorRole::Staff;
                    $showRdoNav = $isRDO || $isStaff;
                @endphp

                @if ($isInstructor)
                    {{-- Instructor Navigation --}}
                    <a href="{{ route('instructor.home') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'instructor.home'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'instructor.home'),
                        ])
                        :title="!sidebarOpen ? 'Dashboard' : null">
                        <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Dashboard</span>
                    </a>
                    <a href="{{ route('instructor.classes') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'instructor.classes*'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'instructor.classes*'),
                        ])
                        :title="!sidebarOpen ? 'My Classes' : null">
                        <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My
                            Classes</span>
                    </a>
                    <a href="{{ route('instructor.groups') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'instructor.groups*'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'instructor.groups*'),
                        ])
                        :title="!sidebarOpen ? 'My Groups' : null">
                        <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Groups</span>
                    </a>
                    @if(Route::has('instructor.schedule-management'))
                    <a href="{{ route('instructor.schedule-management') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'instructor.schedule-management'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'instructor.schedule-management'),
                        ])
                        :title="!sidebarOpen ? 'Schedules' : null">
                        <x-heroicon-o-calendar class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Schedules</span>
                    </a>
                    @endif
                @elseif($isStudent)
                    {{-- Student Navigation --}}
                    <a href="{{ route('student.home') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'student.home'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'student.home'),
                        ])
                        :title="!sidebarOpen ? 'Dashboard' : null">
                        <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Dashboard</span>
                    </a>
                    <a href="{{ route('student.proposal-title') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'student.proposal-title'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'student.proposal-title'),
                        ])
                        :title="!sidebarOpen ? 'Proposals' : null">
                        <x-heroicon-o-newspaper class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Proposals</span>
                    </a>
                    <a href="{{ route('student.group-detail') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'student.group-detail'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'student.group-detail'),
                        ])
                        :title="!sidebarOpen ? 'My Groups' : null">
                        <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Groups</span>
                    </a>
                    <a href="{{ route('student.consultations') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'student.consultations'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'student.consultations'),
                        ])
                        :title="!sidebarOpen ? 'Consultations' : null">
                        <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition
                            class="font-medium whitespace-nowrap">Consultations</span>
                    </a>
                    <a href="{{ route('student.fees') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'student.fees'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'student.fees'),
                        ])
                        :title="!sidebarOpen ? 'Fees' : null">
                        <x-heroicon-o-currency-dollar class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Fees</span>
                    </a>
                @elseif($showRdoNav)
                    {{-- RDO / Staff Navigation --}}
                    <a href="{{ route('rdo.home') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'rdo.home'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'rdo.home'),
                        ])
                        :title="!sidebarOpen ? 'Dashboard' : null">
                        <x-heroicon-o-home class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Dashboard</span>
                    </a>
                    <a href="{{ route('rdo.classes') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'rdo.classes*'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'rdo.classes*'),
                        ])
                        :title="!sidebarOpen ? 'My Classes' : null">
                        <x-heroicon-o-academic-cap class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My
                            Classes</span>
                    </a>

                    <a href="{{ route('rdo.groups') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'rdo.groups*'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'rdo.groups*'),
                        ])
                        :title="!sidebarOpen ? 'My Groups' : null">
                        <x-heroicon-o-user-group class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">My Groups</span>
                    </a>

                    <a href="{{ route('rdo.schedule-management') }}" wire:navigate
                        :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                            'flex items-center gap-3 py-2 rounded-xl transition-all group',
                            'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                'rdo.schedule-management'),
                            'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                'rdo.schedule-management'),
                        ])
                        :title="!sidebarOpen ? 'Schedules' : null">
                        <x-heroicon-o-calendar class="w-6 h-6 flex-shrink-0" />
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Schedules</span>
                    </a>

                    {{-- Management Dropdown --}}
                    <div>
                        <button
                            @click="!sidebarOpen ? (sidebarOpen = true, managementOpen = true) : managementOpen = !managementOpen"
                            :class="sidebarOpen ? 'px-4 justify-between' : 'px-3 justify-center'"
                            class="flex items-center w-full gap-3 py-2 rounded-xl text-blue-200 hover:text-white hover:bg-blue-700/50 transition-all group"
                            :title="!sidebarOpen ? 'Management' : null">

                            <div class="flex items-center gap-3">
                                <x-heroicon-o-squares-2x2 class="w-6 h-6 flex-shrink-0" />
                                <span x-show="sidebarOpen" x-transition
                                    class="font-medium whitespace-nowrap">Management</span>
                            </div>

                            <x-heroicon-o-chevron-down x-show="sidebarOpen" class="w-4 h-4 transition-transform"
                                x-bind:class="managementOpen ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="managementOpen && sidebarOpen" x-collapse class="mt-1 ml-6 space-y-1">
                            <a href="{{ route('rdo.group-masterlist') }}" wire:navigate @class([
                                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                                'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                    'rdo.group-masterlist*'),
                                'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                    'rdo.group-masterlist*'),
                            ])>
                                <x-heroicon-o-user-group class="w-5 h-5 flex-shrink-0" />
                                <span class="font-medium text-sm whitespace-nowrap">Group Masterlist</span>
                            </a>
                            <a href="{{ route('rdo.thesis-fees') }}" wire:navigate @class([
                                'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                                'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                    'rdo.thesis-fees*'),
                                'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                    'rdo.thesis-fees*'),
                            ])>
                                <x-heroicon-o-currency-dollar class="w-5 h-5 flex-shrink-0" />
                                <span class="font-medium text-sm whitespace-nowrap">Thesis Fees</span>
                            </a>
                            <a href="{{ route('rdo.semester-management') }}" wire:navigate
                                @class([
                                    'flex items-center gap-3 px-4 py-2 rounded-xl transition-all',
                                    'bg-white/15 text-white border border-white/25' => request()->routeIs(
                                        'rdo.semester-management*'),
                                    'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                                        'rdo.semester-management*'),
                                ])>
                                <x-heroicon-o-calendar-days class="w-5 h-5 flex-shrink-0" />
                                <span class="font-medium text-sm">Semester Tracking</span>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Repository - All Roles --}}
                <a href="{{ route('repository') }}" wire:navigate
                    :class="sidebarOpen ? 'px-4' : 'px-3 justify-center'" @class([
                        'flex items-center gap-3 py-2 rounded-xl transition-all group',
                        'bg-white/15 text-white border border-white/25' => request()->routeIs(
                            'repository*'),
                        'text-blue-200 hover:text-white hover:bg-blue-700/50' => !request()->routeIs(
                            'repository*'),
                    ])
                    :title="!sidebarOpen ? 'Repository' : null">
                    <x-heroicon-o-book-open class="w-6 h-6 flex-shrink-0" />
                    <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">Repository</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header
                class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/85 backdrop-blur-xl shadow-[0_1px_0_rgba(148,163,184,0.12)]">
                <div class="px-4 sm:px-6">
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <button @click="mobileMenuOpen = !mobileMenuOpen"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:text-cyan-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-500/40 lg:hidden"
                                aria-label="Open navigation menu">
                                <x-heroicon-o-bars-3 class="h-6 w-6" />
                            </button>

                            <button @click="sidebarOpen = !sidebarOpen"
                                class="hidden h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:text-cyan-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-500/40 lg:inline-flex"
                                title="Toggle Sidebar" aria-label="Toggle sidebar">
                                <x-heroicon-o-bars-3 class="h-6 w-6" />
                            </button>


                        </div>

                        <div class="flex min-w-0 items-center gap-3">
                            <div class="hidden md:flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1.5 text-xs font-medium text-cyan-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="truncate">{{ auth()->user()->profileable->role ?? 'Student' }}</span>
                            </div>

                            @livewire('notification-dropdown')

                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open"
                                    class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-500/40">
                                    <div class="hidden md:block text-right">
                                        <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 via-blue-600 to-indigo-600 text-white shadow-md ring-1 ring-white/60">
                                        <span class="text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <x-heroicon-o-chevron-down
                                        class="hidden h-4 w-4 text-slate-400 transition-transform md:block"
                                        x-bind:class="open ? 'rotate-180' : ''" />
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-cloak
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-3 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl ring-1 ring-black/5 z-50">
                                    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                        <div class="mt-2 inline-flex items-center rounded-full bg-cyan-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-800">
                                            {{ auth()->user()->profileable->role ?? 'Student' }}
                                        </div>
                                    </div>

                                    <div class="py-2">
                                        <!-- Menu Items -->
                                        <a href="{{ route('profile') }}" wire:navigate
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-950">
                                            <x-heroicon-o-user class="h-4 w-4 text-slate-500" />
                                            My Profile
                                        </a>
                                        <a href="{{ route('settings') }}" wire:navigate
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-950">
                                            <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-slate-500" />
                                            Settings
                                        </a>

                                        <div class="my-2 border-t border-slate-100"></div>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50">
                                                <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" />
                                                Logout
                                            </button>
                                        </form>
                                    </div>
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
