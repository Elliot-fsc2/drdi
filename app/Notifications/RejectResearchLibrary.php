<?php

namespace App\Notifications;

use App\Models\ResearchLibrary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RejectResearchLibrary extends Notification implements ShouldQueue
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
            'type' => 'research_rejected',
            'title' => 'Research Requires Changes',
            'message' => "Your research \"{$this->researchLibrary->title}\" was declined. Note: {$this->researchLibrary->review_note}",
            'research_library' => [
                'id' => $this->researchLibrary->id,
                'title' => $this->researchLibrary->title,
                'status' => $this->researchLibrary->status?->value,
                'review_note' => $this->researchLibrary->review_note,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
