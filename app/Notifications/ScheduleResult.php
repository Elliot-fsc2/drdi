<?php

namespace App\Notifications;

use App\Models\Schedule;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ScheduleResult extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Schedule $schedule) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'schedule_result',
            'title' => 'Presentation Result',
            'message' => "Your {$this->schedule->presentation_type?->value} result: {$this->statusLabel()}.",
            'icon' => 'heroicon-o-clipboard-document-check',
            'color' => $this->schedule->status?->value === 'passed' ? 'success' : 'danger',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Presentation Result')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your {$this->schedule->presentation_type?->value} result is: {$this->statusLabel()}.")
            ->action('View Details', url('/student/group-detail'));
    }

    private function statusLabel(): string
    {
        return Str::headline($this->schedule->status?->value ?? '');
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
