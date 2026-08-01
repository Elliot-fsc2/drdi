<?php

namespace App\Notifications;

use App\Enums\PersonnelRole;
use App\Models\Group;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersonnelAssigned extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(
        protected Group $group,
        protected PersonnelRole $role,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'personnel_assigned',
            'title' => 'Personnel Assigned',
            'message' => "You have been assigned as {$this->role->value} for {$this->group->name}.",            'icon' => 'heroicon-o-users',
            'color' => 'info',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Personnel Assigned')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("You have been assigned as {$this->role->value} for {$this->group->name}.")
            ->action('View Group', url('/student/group-detail'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
