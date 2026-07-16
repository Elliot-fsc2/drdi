<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class UserSettings
{
    public function changePassword($userId, $newPassword)
    {
        $user = User::findOrFail($userId);
        $user->password_changed_at = now();
        $user->update(['password' => bcrypt($newPassword)]);

        activity('User Change Password')
            ->performedOn($user)
            ->log('Changed password');
    }

    public function showActivityLogs($userId)
    {
        Gate::authorize('view_activity_logs');

        $user = User::findOrFail($userId);
        $activityLogs = Activity::where('causer_id', $userId)->latest()->paginate();

        return $activityLogs;
    }
}
