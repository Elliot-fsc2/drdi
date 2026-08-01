<?php

namespace App\Notifications;

use App\Models\Group;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class GroupStatusChanged extends Notification implements ShouldQueue
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
            'type' => 'group_status_changed',
            'title' => 'Group Status Updated',
            'message' => "Your group {$this->group->name} status has been updated to {$this->statusLabel()}.",
            'icon' => 'heroicon-o-flag',
            'color' => 'info',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Group Status Updated')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your group {$this->group->name} status has been updated to {$this->statusLabel()}.")
            ->action('View Group', url('/student/group-detail'));
    }

    private function statusLabel(): string
    {
        return Str::headline($this->group->status);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
