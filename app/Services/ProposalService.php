<?php

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Proposal;

class ProposalService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function approve(Proposal $proposal, ?string $feedback = null)
    {
        $proposal->update([
            'status' => ProposalStatus::Approved,
            'feedback' => $feedback,
        ]);

        activity('Title Proposal Approved')
            ->performedOn($proposal)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties([
                'group_id' => $proposal->group_id,
                'title' => $proposal->title,
                'feedback' => $feedback,
            ])
            ->log('approved proposal by :causer.name');
    }

    public function reject(Proposal $proposal, ?string $feedback = null)
    {
        $proposal->update([
            'status' => ProposalStatus::Rejected,
            'feedback' => $feedback,
        ]);

        activity('Title Proposal Rejected')
            ->performedOn($proposal)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties([
                'group_id' => $proposal->group_id,
                'title' => $proposal->title,
                'feedback' => $feedback,
            ])
            ->log('rejected proposal by :causer.name');
    }
}
