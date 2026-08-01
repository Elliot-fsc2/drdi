<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResearchPublished extends Notification implements ShouldQueue
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
            'type' => 'research_published',
            'title' => 'Research Published',
            'message' => "Your research \"{$this->library->title}\" has been published to the repository.",            'icon' => 'heroicon-o-book-open',
            'color' => 'success',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Research Published')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your research \"{$this->library->title}\" has been published to the repository.")
            ->action('View Repository', url('/repository'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
