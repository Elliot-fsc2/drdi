<?php

namespace App\Traits;

trait HasEmailPreference
{
    public function viaWithEmail(object $notifiable): array
    {
        return $notifiable->notify_email
            ? ['database', 'mail']
            : ['database'];
    }
}
