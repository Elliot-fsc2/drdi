<?php

namespace App\Notifications;

use App\Models\Consultation;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationBooked extends Notification implements ShouldQueue
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
            'type' => 'consultation_booked',
            'title' => 'Consultation Booked',
            'message' => "You have a consultation booked for {$this->consultation->scheduled_at?->format('M d, Y')} with {$this->consultation->instructor?->full_name}.",            'icon' => 'heroicon-o-chat-bubble-left-right',
            'color' => 'info',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Consultation Booked')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("You have a consultation booked for {$this->consultation->scheduled_at?->format('M d, Y')}.")
            ->line("Instructor: {$this->consultation->instructor?->full_name}")
            ->action('View Consultation', url('/student/consultations'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
