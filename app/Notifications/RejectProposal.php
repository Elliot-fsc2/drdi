<?php

namespace App\Notifications;

use App\Models\Proposal;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectProposal extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Proposal $proposal)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Proposal Rejected')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your proposal has been rejected.')
            ->line('Title: '.$this->proposal->title)
            ->line('Feedback: '.($this->proposal->feedback ?? 'No feedback provided.'))
            ->action('View Details', url('/student/proposal-title'))
            ->line('Please review the feedback and submit a revised proposal.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'proposal_rejected',
            'title' => 'Proposal Rejected',
            'message' => 'Your proposal has been rejected.',
            'proposal' => [
                'id' => $this->proposal->id,
                'title' => $this->proposal->title,
                'status' => $this->proposal->status?->value,
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
