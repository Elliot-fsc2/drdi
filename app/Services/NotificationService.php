<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    public function send(User $user, Notification $notification): void
    {
        if ($user->is(auth()->user())) {
            return;
        }

        $user->notify($notification);
    }

    public function sendMany(Collection $users, Notification $notification): void
    {
        $users = $users->reject(fn (User $user) => $user->is(auth()->user()));

        if ($users->isNotEmpty()) {
            NotificationFacade::send($users, $notification);
        }
    }

    public function sendToGroupMembers(Group $group, Notification $notification): void
    {
        $users = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $group->members()->pluck('students.id'))
            ->get();

        $this->sendMany($users, $notification);
    }

    public function sendToGroupMembersAndAdviser(Group $group, Notification $notification): void
    {
        $studentUsers = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $group->members()->pluck('students.id'))
            ->get();

        $adviserUser = $group->section?->instructor?->user;

        $all = $studentUsers;
        if ($adviserUser !== null) {
            $all->push($adviserUser);
        }

        $this->sendMany($all, $notification);
    }

    public function sendToRdo(Notification $notification): void
    {
        $instructorIds = Instructor::whereIn('role', ['RDO', 'Staff'])->pluck('id');

        $users = User::query()
            ->where('profileable_type', Instructor::class)
            ->whereIn('profileable_id', $instructorIds)
            ->get();

        $this->sendMany($users, $notification);
    }

    public function sendToGroupAdviser(Group $group, Notification $notification): void
    {
        $adviserUser = $group->section?->instructor?->user;
        if ($adviserUser !== null) {
            $this->send($adviserUser, $notification);
        }
    }
}
