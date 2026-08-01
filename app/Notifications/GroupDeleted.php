<?php

namespace App\Notifications;

use App\Models\Group;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GroupDeleted extends Notification implements ShouldQueue
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
            'type' => 'group_deleted',
            'title' => 'Group Deleted',
            'message' => "Your group {$this->group->name} has been deleted.",            'icon' => 'heroicon-o-user-group',
            'color' => 'danger',
        ];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Group Deleted')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your group {$this->group->name} has been deleted.");
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
