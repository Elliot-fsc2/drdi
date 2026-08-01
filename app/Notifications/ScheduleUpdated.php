<?php

namespace App\Notifications;

use App\Models\Schedule;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ScheduleUpdated extends Notification implements ShouldQueue
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
            'type' => 'schedule_updated',
            'title' => 'Schedule Updated',
            'message' => "Your {$this->schedule->presentation_type?->value} schedule has been updated.",
            'icon' => 'heroicon-o-calendar',
            'color' => 'warning',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Schedule Updated')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your {$this->schedule->presentation_type?->value} schedule has been updated.")
            ->line("Date: {$this->schedule->date?->format('M d, Y')}")
            ->line('Time: '.$this->formatTime($this->schedule->start_time).' - '.$this->formatTime($this->schedule->end_time))
            ->line("Venue: {$this->schedule->venue}");
    }

    private function formatTime(?string $time): string
    {
        return $time ? Carbon::parse($time)->format('g:i A') : '';
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
