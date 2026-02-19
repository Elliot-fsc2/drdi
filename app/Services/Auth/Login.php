<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login
{
    public function attempt(string $email, string $password, bool $remember = false)
    {
        $key = $this->throttleKey();
        $maxAttempts = 5;
        $decaySeconds = 60; // lockout duration

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            RateLimiter::clear($key);
            request()->session()->regenerate();

            $user = Auth::user();

            if ($user->profileable?->role === \App\Enums\InstructorRole::RDO) {
                return redirect()->intended(route('rdo.home'));
            }
            // Redirect based on user role
            if ($user->profileable_type === \App\Models\Instructor::class) {
                // Check if RDO
                // Regular instructor
                return redirect()->intended(route('instructor.home'));
            } elseif ($user->profileable_type === \App\Models\Student::class) {
                return redirect()->intended(route('student.home'));
            }

            // Default fallback
            return redirect()->intended('/');
        }

        RateLimiter::hit($key, $decaySeconds);

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    private function throttleKey(): string
    {
        return request()->ip();
    }
}
