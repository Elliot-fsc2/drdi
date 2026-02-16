<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login
{
    public function attempt(string $email, string $password, bool $remember = false)
    {
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
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

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
