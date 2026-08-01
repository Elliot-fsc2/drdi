<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResearchSubmitted extends Notification implements ShouldQueue
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
            'type' => 'research_submitted',
            'title' => 'Research Submitted',
            'message' => "\"{$this->library->title}\" has been submitted for review by {$this->library->group?->name}.",            'icon' => 'heroicon-o-archive-box-arrow-down',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Research Submitted for Review')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("\"{$this->library->title}\" has been submitted for review.")
            ->action('Review Submission', url('/rdo/research-approvals'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
