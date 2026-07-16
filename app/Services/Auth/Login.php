<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login
{
    protected int $maxAttempts = 5;

    protected int $decaySeconds = 60;

    public function attempt(string $email, string $password, bool $remember = false)
    {
        if (! app()->environment('local')) {
            $key = $this->throttleKey();

            if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages([
                    'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
            }
        }

        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            if (! app()->environment('local')) {
                RateLimiter::clear($key);
            }
            request()->session()->regenerate();

            $user = Auth::user();

            $role = $user->profileable?->role;

            // Staff and RDO both go to RDO dashboard
            if ($role === \App\Enums\InstructorRole::RDO || $role === \App\Enums\InstructorRole::Staff) {
                return redirect()->intended(route('rdo.home'));
            }

            // Redirect based on user role
            if ($user->profileable_type === \App\Models\Instructor::class) {
                // Regular instructor
                return redirect()->intended(route('instructor.home'));
            } elseif ($user->profileable_type === \App\Models\Student::class) {
                return redirect()->intended(route('student.home'));
            }

            // Default fallback
            return redirect()->intended('/');
        }

        if (! app()->environment('local')) {
            RateLimiter::hit($key, $this->decaySeconds);
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function getLockoutSeconds(): int
    {
        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            return RateLimiter::availableIn($key);
        }

        return 0;
    }

    private function throttleKey(): string
    {
        return request()->ip();
    }
}
