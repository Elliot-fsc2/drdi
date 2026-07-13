<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApproveResearchLibrary extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected ResearchLibrary $researchLibrary)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'research_approved',
            'title' => 'Research Approved',
            'message' => "Your research \"{$this->researchLibrary->title}\" has been approved and is now publicly visible in the repository.",
            'research_library' => [
                'id' => $this->researchLibrary->id,
                'title' => $this->researchLibrary->title,
                'status' => $this->researchLibrary->status?->value,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
