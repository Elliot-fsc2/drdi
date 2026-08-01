<?php

namespace App\Notifications;

use App\Models\Post;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewAnnouncement extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Post $post) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_announcement',
            'title' => $this->post->title,
            'message' => Str::limit(strip_tags($this->post->content ?? ''), 120),            'icon' => 'heroicon-o-megaphone',
            'color' => 'primary',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->post->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line(Str::limit(strip_tags($this->post->content ?? ''), 200));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
