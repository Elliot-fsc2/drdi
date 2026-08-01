<?php

namespace App\Notifications;

use App\Models\Semester;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SemesterRateUpdated extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Semester $semester) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'semester_rate_updated',
            'title' => 'Thesis Rates Updated',
            'message' => "Your thesis rates for {$this->semester->name} have been updated.",            'icon' => 'heroicon-o-currency-dollar',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Thesis Rates Updated')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your thesis rates for {$this->semester->name} have been updated.")
            ->action('View Fees', url('/student/fees'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
