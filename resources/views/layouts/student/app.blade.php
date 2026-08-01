<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DRDI NCST Research Portal - Department of Research Development and Innovation">
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#0891b2">
  <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">

  <title>{{ $title ? $title . ' • DRDI NCST' : 'DRDI NCST • Research Portal' }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
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

<body class="bg-gray-50" x-data="{ mobileMenuOpen: false }">
  @php
  $navigationItems = [
  ['label' => 'Dashboard', 'url' => route('student.home'), 'active' => 'student.home', 'icon' => 'home'],
  ['label' => 'Proposals', 'url' => route('student.proposal-title'), 'active' => 'student.proposal-title', 'icon' =>
  'newspaper'],
  ['label' => 'My Groups', 'url' => route('student.group-detail'), 'active' => 'student.group-detail', 'icon' =>
  'user-group'],
  ['label' => 'Consultations', 'url' => route('student.consultations'), 'active' => 'student.consultations', 'icon' =>
  'chat'],
  ['label' => 'Fees', 'url' => route('student.fees'), 'active' => 'student.fees', 'icon' => 'currency-dollar'],
  ];
  @endphp

  <div class="min-h-screen">
    <header
      class="sticky top-0 z-30 border-b border-blue-700/50 bg-blue-800">
      <div class="relative">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">

          <a href="{{ route('student.home') }}" wire:navigate
            class="hidden lg:flex items-center gap-3 rounded-2xl px-2 py-1 transition hover:bg-blue-700/50">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 shadow-md ring-1 ring-white/25">
              <img src="{{ asset('images/logo.png') }}" alt="DRDI Logo" class="h-10 w-10 rounded-xl">
            </div>
            <div class="hidden sm:block">
              <h1 class="text-sm font-semibold tracking-wide text-white">DRDI NCST</h1>
            </div>
          </a>

          <button @click="mobileMenuOpen = !mobileMenuOpen"
            class="flex lg:hidden items-center gap-3 rounded-2xl px-2 py-1 transition hover:bg-blue-700/50 focus:outline-none">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 shadow-md ring-1 ring-white/25">
              <img src="{{ asset('images/logo.png') }}" alt="DRDI Logo" class="h-10 w-10 rounded-xl">
            </div>
            <div class="hidden sm:block">
              <h1 class="text-sm font-semibold tracking-wide text-white">DRDI NCST</h1>
            </div>
          </button>

          <nav class="hidden flex-1 items-center justify-center gap-2 lg:flex">
            @foreach ($navigationItems as $item)
            <a href="{{ $item['url'] }}" wire:navigate
              @class([ 'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition-all'
              , 'bg-white/15 text-white border border-white/25'=> request()->routeIs($item['active']),
              'text-blue-200 hover:bg-blue-700/50 hover:text-white' =>
              !request()->routeIs($item['active']),
              ])>
              @switch($item['icon'])
              @case('home')
              <x-heroicon-o-home class="h-5 w-5 shrink-0" />
              @break
              @case('newspaper')
              <x-heroicon-o-newspaper class="h-5 w-5 shrink-0" />
              @break
              @case('user-group')
              <x-heroicon-o-user-group class="h-5 w-5 shrink-0" />
              @break
              @case('chat')
              <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 shrink-0" />
              @break
              @case('currency-dollar')
              <x-heroicon-o-currency-dollar class="h-5 w-5 shrink-0" />
              @break
              @endswitch
              <span>{{ $item['label'] }}</span>
            </a>
            @endforeach

            <a href="{{ route('repository') }}" wire:navigate
              @class([ 'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition-all'
              , 'bg-white/15 text-white border border-white/25'=> request()->routeIs('repository*'),
              'text-blue-200 hover:bg-blue-700/50 hover:text-white' =>
              !request()->routeIs('repository*'),
              ])>
              <x-heroicon-o-book-open class="h-5 w-5 shrink-0" />
              <span>Repository</span>
            </a>
          </nav>

          <div class="flex items-center gap-2 sm:gap-3 ml-auto lg:ml-0">

            @livewire('notification-dropdown')

            <div class="relative" x-data="{ open: false }" @click.away="open = false">
              <button @click="open = !open"
                class="flex items-center gap-3 rounded-2xl border border-blue-600/50 bg-blue-700/30 px-3 py-2 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                <div
                  class="flex h-10 w-10 items-center justify-center rounded-xl overflow-hidden bg-white/15 shadow-md ring-1 ring-white/25">
                  <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}"
                    alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                </div>

                <x-heroicon-o-chevron-down class="hidden h-4 w-4 text-blue-300 transition-transform md:block"
                  x-bind:class="open ? 'rotate-180' : ''" />
              </button>

              <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 z-50 mt-3 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl ring-1 ring-black/5">

                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                  <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                  <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                </div>
                <div class="py-1.5">
                  <a href="{{ route('profile') }}" wire:navigate
                    class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-950">
                    <x-heroicon-o-user class="h-4 w-4 text-slate-500" />
                    My Profile
                  </a>

                  <a href="{{ route('settings') }}" wire:navigate
                    class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-950">
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-slate-500" />
                    Settings
                  </a>

                  <div class="my-1 border-t border-slate-100"></div>

                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                      class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50">
                      <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" />
                      Logout
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition-opacity ease-linear duration-200"
          x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
          x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0" @click="mobileMenuOpen = false"
          class="fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="-translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
          x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100"
          x-transition:leave-end="-translate-y-2 opacity-0"
          class="absolute inset-x-0 top-full z-30 border-b border-blue-700/50 bg-blue-800 shadow-xl lg:hidden">
          <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="space-y-2">
              @foreach ($navigationItems as $item)
              <a href="{{ $item['url'] }}" wire:navigate @click="mobileMenuOpen = false"
                @class([ 'flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-medium transition-all'
                , 'bg-white/15 text-white border border-white/25'=> request()->routeIs($item['active']),
                'border-blue-700/50 text-blue-200 hover:bg-blue-700/50 hover:text-white' =>
                !request()->routeIs($item['active']),
                ])>
                @switch($item['icon'])
                @case('home')
                <x-heroicon-o-home class="h-5 w-5 shrink-0" />
                @break
                @case('newspaper')
                <x-heroicon-o-newspaper class="h-5 w-5 shrink-0" />
                @break
                @case('user-group')
                <x-heroicon-o-user-group class="h-5 w-5 shrink-0" />
                @break
                @case('chat')
                <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 shrink-0" />
                @break
                @case('currency-dollar')
                <x-heroicon-o-currency-dollar class="h-5 w-5 shrink-0" />
                @break
                @endswitch
                <span>{{ $item['label'] }}</span>
              </a>
              @endforeach

              <a href="{{ route('repository') }}" wire:navigate @click="mobileMenuOpen = false"
                @class([ 'flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-medium transition-all'
                , 'bg-white/15 text-white border border-white/25'=> request()->routeIs('repository*'),
                'border-blue-700/50 text-blue-200 hover:bg-blue-700/50 hover:text-white' =>
                !request()->routeIs('repository*'),
                ])>
                <x-heroicon-o-book-open class="h-5 w-5 shrink-0" />
                <span>Repository</span>
              </a>
            </nav>
          </div>
        </div>
      </div>
    </header>

    <main class="p-4 sm:p-6 lg:p-8">
      {{ $slot }}
    </main>
  </div>

  @livewireScripts
  @filamentScripts
</body>

</html>
