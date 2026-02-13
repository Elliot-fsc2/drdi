<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    // Redirect based on user role
    if ($user->profileable_type === \App\Models\Instructor::class) {
        // Check if RDO
        if ($user->profileable?->role === \App\Enums\InstructorRole::RDO) {
            return redirect()->route('rdo.home');
        }

        // Regular instructor
        return redirect()->route('instructor.home');
    } elseif ($user->profileable_type === \App\Models\Student::class) {
        return redirect()->route('student.home');
    }

    // Fallback to login if no valid role
    Auth::logout();

    return redirect()->route('login')->with('error', 'Invalid user role');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();

        return redirect()->route('login');
    })->name('logout');
});

Route::middleware(['auth', 'instructor'])
    ->prefix('instructor')
    ->as('instructor.')
    ->group(function () {
        Route::livewire('/home', 'instructor::home')->name('home');
    });

Route::middleware(['auth', 'student'])
    ->prefix('student')
    ->as('student.')
    ->group(function () {
        Route::livewire('/home', 'student::home')->name('home');
    });

Route::middleware(['auth', 'rdo'])
    ->prefix('rdo')
    ->as('rdo.')
    ->group(function () {
        Route::livewire('/home', 'rdo::home')->name('home');
    });
