<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\ForgotPassword as ForgotPasswordNotification;
use Illuminate\Support\Facades\Password;

class ForgotPassword
{
    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        $token = Password::broker()->createToken($user);

        $user->notify(new ForgotPasswordNotification($token));
    }
}
