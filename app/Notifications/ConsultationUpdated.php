<?php

namespace App\Notifications;

use App\Models\Consultation;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationUpdated extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Consultation $consultation) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'consultation_updated',
            'title' => 'Consultation Updated',
            'message' => "Your consultation scheduled for {$this->consultation->scheduled_at?->format('M d, Y')} has been updated.",            'icon' => 'heroicon-o-chat-bubble-left-right',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Consultation Updated')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your consultation scheduled for {$this->consultation->scheduled_at?->format('M d, Y')} has been updated.")
            ->action('View Consultation', url('/student/consultations'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
