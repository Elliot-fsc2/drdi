<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectResearchLibrary extends Notification implements ShouldQueue
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
            ->subject('Research Requires Changes')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your research requires changes before it can be approved.')
            ->line('Title: '.$this->researchLibrary->title)
            ->line('Review Note: '.($this->researchLibrary->review_note ?? 'No additional notes.'))
            ->action('View Details', url('/repository'))
            ->line('Please make the necessary revisions and resubmit.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'research_rejected',
            'title' => 'Research Requires Changes',
            'message' => "Your research \"{$this->researchLibrary->title}\" was declined. Note: {$this->researchLibrary->review_note}",
            'research_library' => [
                'id' => $this->researchLibrary->id,
                'title' => $this->researchLibrary->title,
                'status' => $this->researchLibrary->status?->value,
                'review_note' => $this->researchLibrary->review_note,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
