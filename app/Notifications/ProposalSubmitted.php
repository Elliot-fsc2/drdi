<?php

namespace App\Notifications;

use App\Models\Proposal;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalSubmitted extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Proposal $proposal) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'proposal_submitted',
            'title' => 'New Proposal Submitted',
            'message' => "A new proposal titled \"{$this->proposal->title}\" has been submitted by {$this->proposal->group?->name}.",
            'icon' => 'heroicon-o-newspaper',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Proposal Submitted')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("A new proposal titled \"{$this->proposal->title}\" has been submitted.")
            ->line("Group: {$this->proposal->group?->name}")
            ->action('Review Proposal', url('/instructor/classes'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
