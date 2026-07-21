<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetInitialPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.set', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Set Your Password - DRDI NCST')
            ->line('Your account has been created successfully.')
            ->line('Please click the button below to set your password and activate your account.')
            ->action('Set Password', $url)
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not expect this email, no further action is required.');
    }
}
