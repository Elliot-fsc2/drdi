<?php

namespace App\Notifications;

use App\Models\Group;
use App\Traits\HasEmailPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeLedgerInitialized extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function __construct(protected Group $group) {}

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'fee_ledger_initialized',
            'title' => 'Fee Ledger Created',
            'message' => "Your group's fee ledger has been created.",            'icon' => 'heroicon-o-currency-dollar',
            'color' => 'primary',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fee Ledger Created')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your group's fee ledger has been created.")
            ->action('View Fees', url('/student/fees'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
