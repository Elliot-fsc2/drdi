<?php

use App\Services\Auth\ForgotPassword;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')]
  #[Title('Forgot Password')]
  class extends Component
  {
      #[Validate('required|string|email')]
      public string $email = '';

      public bool $sent = false;

      public function sendResetLink(ForgotPassword $forgotPasswordService)
      {
          $this->validate();

          try {
              $forgotPasswordService->sendResetLink($this->email);

              $this->sent = true;
          } catch (\Illuminate\Validation\ValidationException $e) {
              $this->addError('email', $e->validator->errors()->first('email'));
          }
      }
  };
?>
<div class="h-screen flex overflow-hidden">
  <div
    class="hidden lg:flex lg:w-1/2 bg-linear-to-br from-blue-400 via-cyan-300 to-blue-600 relative overflow-hidden">
    <div class="absolute inset-0 bg-linear-to-br from-blue-500/80 via-cyan-400/60 to-blue-600/80"></div>

    <div class="absolute top-10 left-10 w-32 h-32 bg-white/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 right-20 w-40 h-40 bg-blue-300/30 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-cyan-200/20 rounded-full blur-2xl"></div>

    <div class="relative z-10 flex flex-col justify-center items-center px-8 xl:px-16 text-white w-full py-8">
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

      <div class="flex gap-2 mt-8">
        <div class="w-2 h-2 bg-white rounded-full"></div>
        <div class="w-2 h-2 bg-white/40 rounded-full"></div>
        <div class="w-2 h-2 bg-white/40 rounded-full"></div>
      </div>
    </div>
  </div>

  <div class="flex-1 flex items-center justify-center px-6 lg:px-12 xl:px-16 bg-gray-50 overflow-y-auto">
    <div class="w-full max-w-md py-8">
      <div class="bg-white rounded-2xl shadow-xl p-8 xl:p-10">
        @if ($sent)
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Check Your Email</h2>
            <p class="text-gray-500 mb-6">
              If an account exists with that email address, we have sent a password reset link. Please check your inbox and follow the instructions.
            </p>
            <a href="{{ route('login') }}"
              class="inline-block w-full text-center text-white font-semibold py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition-all shadow-lg">
              Back to Login
            </a>
          </div>
        @else
          <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password</h2>
            <p class="text-sm text-gray-500">Enter your email address and we will send you a link to reset your password.</p>
          </div>

          <form wire:submit="sendResetLink" class="space-y-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
              </label>
              <input type="email" id="email" wire:model="email" placeholder="Enter your email address"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                required>
              @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <button type="submit"
              class="w-full text-white font-semibold py-3 rounded-lg transition-all shadow-lg bg-blue-600 hover:bg-blue-700">
              Send Reset Link
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-gray-500">
            Remember your password?
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">
              Back to Login
            </a>
          </p>
        @endif
      </div>
    </div>
  </div>
</div>
