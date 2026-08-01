<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ResearchUnpublished extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected ResearchLibrary $library) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'research_unpublished',
            'title' => 'Research Unpublished',
            'message' => "Your research \"{$this->library->title}\" has been unpublished from the repository.",            'icon' => 'heroicon-o-book-open',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Research Unpublished')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your research \"{$this->library->title}\" has been unpublished from the repository.");
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
