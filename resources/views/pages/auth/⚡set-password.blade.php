<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')]
  #[Title('Set Password')]
  class extends Component
  {
      #[Validate('required|string')]
      public string $token = '';

      #[Validate('required|string|email')]
      public string $email = '';

      #[Validate('required|string|confirmed')]
      public string $password = '';

      #[Validate('required|string')]
      public string $password_confirmation = '';

      public bool $success = false;

      public function mount()
      {
          $this->token = request('token');
          $this->email = request('email');

          if (! $this->token || ! $this->email) {
              $this->success = false;
          }
      }

      public function setPassword()
      {
          $this->validate();

          $status = Password::broker()->reset(
              [
                  'email' => $this->email,
                  'password' => $this->password,
                  'password_confirmation' => $this->password_confirmation,
                  'token' => $this->token,
              ],
              function ($user, $password) {
                  $user->forceFill(['password' => bcrypt($password)])->save();

                  event(new PasswordReset($user));
              }
          );

          if ($status === Password::PASSWORD_RESET) {
              $this->success = true;
          } elseif ($status === Password::INVALID_TOKEN) {
              $this->addError('token', 'This password reset link has expired or is invalid. Please contact your administrator.');
          } else {
              $this->addError('email', 'We could not verify your email address. Please contact your administrator.');
          }
      }
  };
?>
<div class="h-screen flex overflow-hidden">
  <div class="flex-1 flex items-center justify-center px-6 lg:px-12 xl:px-16 bg-gray-50 overflow-y-auto">
    <div class="w-full max-w-md py-8">
      <div class="bg-white rounded-2xl shadow-xl p-8 xl:p-10">
        @if ($success)
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Password Set Successfully!</h2>
            <p class="text-gray-500 mb-6">Your password has been set. You can now login with your new password.</p>
            <a href="{{ route('login') }}"
              class="inline-block w-full text-center text-white font-semibold py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition-all shadow-lg">
              Go to Login
            </a>
          </div>
        @elseif (! $token || ! $email)
          <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Invalid Link</h2>
            <p class="text-gray-500 mb-6">This password setup link is invalid or incomplete. Please contact your administrator.</p>
            <a href="{{ route('login') }}"
              class="inline-block w-full text-center text-white font-semibold py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition-all shadow-lg">
              Go to Login
            </a>
          </div>
        @else
          <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Set Your Password</h2>
            <p class="text-sm text-gray-500">Choose a strong password for your account</p>
          </div>

          <form wire:submit="setPassword" class="space-y-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input type="email" id="email" wire:model="email" readonly disabled
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
              <input type="password" id="password" wire:model="password" placeholder="Enter new password"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                required>
              @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
              <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Confirm new password"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                required>
              @error('password_confirmation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            @error('token')
              <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit"
              class="w-full text-white font-semibold py-3 rounded-lg transition-all shadow-lg bg-blue-600 hover:bg-blue-700">
              Set Password
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>
