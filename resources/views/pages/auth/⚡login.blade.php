<?php

use App\Services\Auth\Login;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')]
  #[Title('Login')]
  class extends Component
  {
      #[Validate('required|string')]
      public string $email = '';

      #[Validate('required|string')]
      public string $password = '';

      public bool $remember = false;

      public function login(Login $loginService)
      {
          $this->validate();

          try {
              $loginService->attempt($this->email, $this->password, $this->remember);
          } catch (\Illuminate\Validation\ValidationException $e) {
              if (! app()->environment('local') && ($seconds = $loginService->getLockoutSeconds())) {
                  $this->dispatch('lockout', seconds: $seconds);
              }

              throw $e; // Re-throw so the error message shows on the input
          }

          $this->reset(['password']);
      }
  };
?>
<div class="h-screen flex overflow-hidden"@if (! app()->environment('local'))
     x-data="{
        secondsLeft: 0,
        startTimer(s) {
            this.secondsLeft = s;
            let timer = setInterval(() => {
                if (this.secondsLeft <= 0) clearInterval(timer);
                else this.secondsLeft--;
            }, 1000);
        }
     }" @lockout.window="startTimer($event.detail.seconds)"
     @endif>

  <!-- Left Side - Welcome Section -->
  <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
    <!-- Educational Background Photo -->
    <img src="{{ asset('images/education.jpg') }}" alt="Students collaborating in a classroom"
      class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/50 via-blue-800/35 to-blue-950/60"></div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col justify-center items-center px-8 xl:px-16 text-white w-full py-8">

      <h1 class="text-2xl xl:text-4xl font-bold mb-3 text-center drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">
        Welcome to DRDI NCST
      </h1>
      <p class="text-base xl:text-lg text-center max-w-md text-white drop-shadow-[0_1px_4px_rgba(0,0,0,0.9)]">
        Department of Research and Development Innovation - National College of Science and Technology
      </p>

      <!-- Carousel Indicators -->
      <div class="flex gap-2 mt-8">
        <div class="w-2 h-2 bg-white rounded-full"></div>
        <div class="w-2 h-2 bg-white/40 rounded-full"></div>
        <div class="w-2 h-2 bg-white/40 rounded-full"></div>
      </div>
    </div>
  </div>

  <!-- Right Side - Login Form -->
  <div class="flex-1 flex items-center justify-center px-6 lg:px-12 xl:px-16 bg-gray-50 overflow-y-auto">
    <div class="w-full max-w-md py-8">
      <div class="bg-white rounded-2xl shadow-xl p-8 xl:p-10">
        <div class="mb-8">
          <div class="flex justify-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="DRDI NCST Logo" class="h-16 w-16 xl:h-20 xl:w-20 object-contain">
          </div>
          <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Login</h2>
          <p class="text-sm text-gray-500 text-center">Access your DRDI NCST account</p>
        </div>

        <form wire:submit="login" class="space-y-6">
          <!-- Email or Username -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email or Username
            </label>
            <input type="text" id="email" wire:model="email" placeholder="Enter Email or Username"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
              required>
            @error('email')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <input type="password" id="password" wire:model="password" placeholder="Enter Password"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
              required>
            @error('password')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center">
              <input type="checkbox" wire:model="remember"
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Remember Me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
              Forget Password ?
            </a>
          </div>

          <!-- Login Button -->
          <button type="submit"
            @if (! app()->environment('local'))
            x-bind:disabled="secondsLeft > 0"
            x-bind:class="secondsLeft > 0 ? 'bg-gray-400 opacity-50 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
            @endif
            class="w-full text-white font-semibold py-3 rounded-lg transition-all shadow-lg bg-blue-600 hover:bg-blue-700">
            @if (! app()->environment('local'))
            <span x-show="secondsLeft <= 0">Login</span>
            <span x-show="secondsLeft > 0" style="display: none;">
              Please wait <span x-text="secondsLeft"></span>s
            </span>
            @else
            <span>Login</span>
            @endif
          </button>
        </form>
      </div>
    </div>
  </div>
</div>