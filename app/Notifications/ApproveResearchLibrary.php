<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApproveResearchLibrary extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected ResearchLibrary $researchLibrary)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Research Approved')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your research has been approved and is now publicly visible in the repository.')
            ->line('Title: '.$this->researchLibrary->title)
            ->action('View Repository', url('/repository'))
            ->line('Thank you for your contribution!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'research_approved',
            'title' => 'Research Approved',
            'message' => "Your research \"{$this->researchLibrary->title}\" has been approved and is now publicly visible in the repository.",
            'research_library' => [
                'id' => $this->researchLibrary->id,
                'title' => $this->researchLibrary->title,
                'status' => $this->researchLibrary->status?->value,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
