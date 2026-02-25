<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return redirect()->route('login');
});

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

Route::livewire('/test', 'sorting')->name('test');

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
        Route::livewire('/classes', 'instructor::my-classes')->name('classes');
        Route::livewire('/classes/{section}', 'instructor::my-classes.view')->name('classes.view');
        Route::livewire('/classes/{section}/group/create', 'instructor::my-classes.group.create')->name('classes.group.create');
        Route::livewire('/classes/{section}/group/{group}', 'instructor::my-classes.group.view')->name('classes.group.view');
        Route::livewire('/classes/{section}/group/{group}/proposals', 'instructor::my-classes.group.proposals')->name('classes.group.proposals');
        Route::livewire('/classes/{section}/group/{group}/schedule', 'instructor::my-classes.schedule')->name('classes.schedule');

        Route::livewire('/groups', 'instructor::groups')->name('groups');
        Route::livewire('/groups/{group}', 'instructor::groups.assigned.view')->name('groups.assigned.view');
    });

Route::middleware(['auth', 'student'])
    ->prefix('student')
    ->as('student.')
    ->group(function () {
        Route::livewire('/home', 'student::home')->name('home');
        Route::livewire('/group-detail', 'student::group-detail')->name('group-detail');
        Route::livewire('/proposal-title', 'student::proposal-title')->name('proposal-title');
        Route::livewire('/consultations', 'student::consultations')->name('consultations');
        Route::livewire('/fees', 'student::fees')->name('fees');
    });

Route::middleware(['auth', 'rdo'])
    ->prefix('rdo')
    ->as('rdo.')
    ->group(function () {
        Route::livewire('/home', 'rdo::home')->name('home');
        Route::livewire('/classes', 'instructor::my-classes')->name('classes');
        Route::livewire('/classes/{section}', 'instructor::my-classes.view')->name('classes.view');
        Route::livewire('/classes/{section}/group/create', 'instructor::my-classes.group.create')->name('classes.group.create');
        Route::livewire('/classes/{section}/group/{group}', 'instructor::my-classes.group.view')->name('classes.group.view');
        Route::livewire('/classes/{section}/group/{group}/proposals', 'instructor::my-classes.group.proposals')->name('classes.group.proposals');
        Route::livewire('/classes/{section}/group/{group}/schedule', 'instructor::my-classes.schedule')->name('classes.schedule');
        Route::livewire('/group-masterlist', 'rdo::management.group-masterlist')->name('group-masterlist');
        Route::livewire('/thesis-fees', 'rdo::management.thesis-fees')->name('thesis-fees');
        Route::livewire('/semester-management', 'rdo::management.semester-tracking')->name('semester-management');

        Route::livewire('/groups', 'instructor::groups')->name('groups');
        Route::livewire('/groups/{group}', 'instructor::groups.assigned.view')->name('groups.assigned.view');
    });
