<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\ForgotPassword as ForgotPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPassword
{
    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find an account with that email address.',
            ]);
        }

        $token = Password::broker()->createToken($user);

        $user->notify(new ForgotPasswordNotification($token));
    }
}
