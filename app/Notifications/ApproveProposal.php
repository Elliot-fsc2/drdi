<?php

namespace App\Notifications;

use App\Models\Proposal;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApproveProposal extends Notification implements ShouldQueue
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
            ->subject('Proposal Approved')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your proposal has been approved.')
            ->line('Title: '.$this->proposal->title)
            ->action('View Proposal', url('/student/proposal-title'))
            ->line('Congratulations on your approved proposal!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'proposal_approved',
            'title' => 'Proposal Approved',
            'message' => 'Your proposal has been approved.',
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
