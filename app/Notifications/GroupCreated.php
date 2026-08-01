<?php

namespace App\Notifications;

use App\Models\Group;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupCreated extends Notification implements ShouldQueue
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
            'type' => 'group_created',
            'title' => 'New Group Created',
            'message' => "You have been added to {$this->group->name}.",            'icon' => 'heroicon-o-user-group',
            'color' => 'primary',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Group Created')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("You have been added to {$this->group->name}.")
            ->action('View Group', url('/student/group-detail'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
