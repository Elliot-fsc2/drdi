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

    $role = $user->profileable?->role;

    // Staff and RDO both go to RDO dashboard
    if ($role === \App\Enums\InstructorRole::RDO || $role === \App\Enums\InstructorRole::Staff) {
        return redirect()->route('rdo.home');
    }

    // Redirect based on user role
    if ($user->profileable_type === \App\Models\Instructor::class) {
        // Regular instructor
        return redirect()->route('instructor.home');
    } elseif ($user->profileable_type === \App\Models\Student::class) {
        return redirect()->route('student.home');
    } elseif ($user->is_admin) {
        return redirect('admin');
    }

    // Fallback to login if no valid role
    Auth::logout();

    return redirect()->route('login')->with('error', 'Invalid user role');
});

Route::livewire('/test', 'task-board')->name('test');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
    Route::livewire('/forgot-password', 'pages::auth.forgot-password')->name('password.request');
    Route::livewire('/set-password', 'pages::auth.set-password')->name('password.set');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();

        return redirect()->route('login');
    })->name('logout');

    Route::livewire('/profile', 'pages::profile')->name('profile');
    Route::livewire('/settings', 'pages::account-settings')->name('settings');
    Route::livewire('/repository', 'pages::repository')->name('repository');
    Route::livewire('/repository/{researchlibrary:title}', 'pages::repository.details')->name('repository.details');

    Route::livewire('/groups/{group}/repository-form', 'instructor::library-requirement')->name('repository-requirement');
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
        Route::livewire('/classes/{section}/group/{group}/schedule/{schedule}', 'instructor::my-classes.schedule.details')->name('classes.schedule.details');
        Route::livewire('/classes/{section}/group/{group}/settings', 'instructor::groups.settings')->name('group.settings');

        Route::livewire('/announcements/create', 'instructor::announcement.create')->name('announcements.create');
        Route::livewire('/section-announcements/create', 'instructor::announcement.section-create')->name('announcements.section-create');
        Route::livewire('/section-announcements/{post}/edit', 'instructor::announcement.edit')->name('announcements.edit');

        Route::livewire('/groups', 'instructor::groups')->name('groups');
        Route::livewire('/groups/{group}', 'instructor::groups.assigned.view')->name('groups.assigned.view');

        Route::livewire('/library-submissions', 'instructor::library-submissions')->name('library-submissions');
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
        Route::livewire('/classes', 'rdo::my-classes')->name('classes');
        Route::livewire('/classes/{section}', 'rdo::my-classes.view')->name('classes.view');
        Route::livewire('/classes/{section}/group/create', 'rdo::my-classes.group.create')->name('classes.group.create');
        Route::livewire('/classes/{section}/group/{group}', 'rdo::my-classes.group.view')->name('classes.group.view');
        Route::livewire('/classes/{section}/group/{group}/proposals', 'instructor::my-classes.group.proposals')->name('classes.group.proposals');
        Route::livewire('/classes/{section}/group/{group}/schedule', 'rdo::my-classes.schedule')->name('classes.schedule');
        Route::livewire('/classes/{section}/group/{group}/schedule/{schedule}', 'rdo::my-classes.schedule.details')->name('classes.schedule.details');
        Route::livewire('/classes/{section}/group/{group}/settings', 'rdo::groups.settings')->name('group.settings');

        Route::livewire('/announcements', 'rdo::announcement')->name('announcements');
        Route::livewire('/announcements/create', 'rdo::announcement.create')->name('announcements.create');
        Route::livewire('/section-announcements/create', 'rdo::announcement.section-create')->name('section-create.announcements');
        Route::livewire('/announcements/{post}/edit', 'rdo::announcement.edit')->name('announcements.edit');

        Route::livewire('/group-masterlist', 'rdo::management.group-masterlist')->name('group-masterlist');
        Route::livewire('/group-masterlist/{group}', 'rdo::groups.view')->name('group-masterlist.view');
        Route::livewire('/thesis-fees', 'rdo::management.thesis-fees')->name('thesis-fees');
        Route::livewire('/semester-management', 'rdo::management.semester-tracking')->name('semester-management');
        Route::livewire('/schedule-management', 'rdo::management.schedules')->name('schedule-management');
        Route::livewire('/repository-management', 'rdo::management.repository-management')->name('repository-management');
        Route::livewire('/research-approvals', 'rdo::management.research-approvals')->name('research-approvals');

        Route::livewire('/groups', 'rdo::groups')->name('groups');
        Route::livewire('/groups/{group}', 'rdo::groups.assigned.view')->name('groups.assigned.view');

        Route::livewire('/library-submissions', 'instructor::library-submissions')->name('library-submissions');
        Route::livewire('/groups/{group}/repository-form', 'instructor::library-requirement')->name('repository-requirement');
    });
