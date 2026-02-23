<?php

use App\Services\Auth\Login;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')]
  #[Title('Login')]
  class extends Component {
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
      if ($seconds = $loginService->getLockoutSeconds()) {
        $this->dispatch('lockout', seconds: $seconds);
      }

      throw $e; // Re-throw so the error message shows on the input
    }

    $this->reset(['password']);
  }
};
?>
<div class="h-screen flex overflow-hidden" x-data="{
        secondsLeft: 0,
        startTimer(s) {
            this.secondsLeft = s;
            let timer = setInterval(() => {
                if (this.secondsLeft <= 0) clearInterval(timer);
                else this.secondsLeft--;
            }, 1000);
        }
     }" @lockout.window="startTimer($event.detail.seconds)">

  <!-- Left Side - Welcome Section -->
  <div
    class="hidden lg:flex lg:w-1/2 bg-linear-to-br from-orange-400 via-pink-300 to-purple-400 relative overflow-hidden">
    <div class="absolute inset-0 bg-linear-to-br from-orange-500/80 via-pink-400/60 to-purple-500/80"></div>

    <!-- Decorative Elements -->
    <div class="absolute top-10 left-10 w-32 h-32 bg-white/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 right-20 w-40 h-40 bg-purple-300/30 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-pink-200/20 rounded-full blur-2xl"></div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col justify-center items-center px-8 xl:px-16 text-white w-full py-8">
      <!-- Illustration Placeholder -->
      <div class="mb-6 text-center">
        <div
          class="w-64 h-64 xl:w-80 xl:h-80 bg-white/10 rounded-3xl backdrop-blur-sm flex items-center justify-center mb-6 shadow-2xl">
          <img src="{{ asset('images/logo.png') }}" alt="">
        </div>
      </div>

      <h1 class="text-2xl xl:text-4xl font-bold mb-3 text-center drop-shadow-lg">
        Welcome to DRDI NCST
      </h1>
      <p class="text-base xl:text-lg text-center max-w-md text-white/90 drop-shadow">
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
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Login</h2>
          <p class="text-sm text-gray-500">Access your DRDI NCST account</p>
        </div>

        <form wire:submit="login" class="space-y-6">
          <!-- Email or Username -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email or Username
            </label>
            <input type="text" id="email" wire:model="email" placeholder="Enter Email or Username"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all"
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
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all"
              required>
            @error('password')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center">
              <input type="checkbox" wire:model="remember"
                class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
              <span class="ml-2 text-sm text-gray-700">Remember Me</span>
            </label>
            <a href="#" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
              Forget Password ?
            </a>
          </div>

          <!-- Login Button -->
          <button type="submit" x-bind:disabled="secondsLeft > 0"
            x-bind:class="secondsLeft > 0 ? 'bg-gray-400 opacity-50 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700'"
            class="w-full text-white font-semibold py-3 rounded-lg transition-all shadow-lg">

            <span x-show="secondsLeft <= 0">Login</span>
            <span x-show="secondsLeft > 0">
              Please wait <span x-text="secondsLeft"></span>s
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>