<?php

namespace App\Notifications;

use App\Models\Group;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRemoved extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Group $group) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'member_removed',
            'title' => 'Removed from Group',
            'message' => "You have been removed from {$this->group->name}.",            'icon' => 'heroicon-o-user-minus',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Removed from Group')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("You have been removed from {$this->group->name}.");
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
