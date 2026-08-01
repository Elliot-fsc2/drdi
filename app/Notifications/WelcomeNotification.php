<?php

namespace App\Notifications;

use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected string $tempPassword) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to DRDI Research Portal')
            ->greeting('Welcome '.$notifiable->name.'!')
            ->line('Your account has been created successfully.')
            ->line('Your temporary password is: '.$this->tempPassword)
            ->action('Login Now', url('/login'))
            ->line('Please change your password after logging in.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to DRDI',
            'message' => 'Your account has been created successfully.',            'icon' => 'heroicon-o-hand-wave',
            'color' => 'success',
        ];
    }
}
