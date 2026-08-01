<?php

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Group;
use App\Models\Proposal;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ApproveProposal;
use App\Notifications\ProposalSubmitted;
use App\Notifications\RejectProposal;

class ProposalService
{
    public function __construct(protected NotificationService $notificationService) {}

    public function update(Proposal $proposal, array $data): Proposal
    {
        $user = auth()->user();
        $student = $user->profileable;

        if (! $user->can('create_proposals') && $student !== null) {
            $isLeader = Group::where('leader_id', $student->id)->exists();
            if ($isLeader) {
                $user->givePermissionTo('create_proposals');
            }
        }

        $proposal->update($data);

        return $proposal->fresh();
    }

    public function approve(Proposal $proposal, ?string $feedback = null)
    {
        $proposal->update([
            'status' => ProposalStatus::APPROVED,
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

        $memberIds = $proposal->group
            ? $proposal->group->members()->pluck('students.id')
            : collect();

        $recipients = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $memberIds)
            ->get();

        $this->notificationService->sendMany($recipients, new ApproveProposal($proposal));
    }

    public function reject(Proposal $proposal, ?string $feedback = null)
    {
        $proposal->update([
            'status' => ProposalStatus::REJECTED,
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

        $memberIds = $proposal->group
            ? $proposal->group->members()->pluck('students.id')
            : collect();

        $recipients = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $memberIds)
            ->get();

        $this->notificationService->sendMany($recipients, new RejectProposal($proposal));
    }

    public function create(array $data): Proposal
    {
        $user = auth()->user();
        $student = $user->profileable;

        if (! $user->can('create_proposals') && $student !== null) {
            $isLeader = Group::where('leader_id', $student->id)->exists();
            if ($isLeader) {
                $user->givePermissionTo('create_proposals');
            }
        }

        $proposal = Proposal::create($data);

        activity('Title Proposal Created')
            ->performedOn($proposal)
            ->causedBy(auth()->user())
            ->event('created')
            ->withProperties([
                'group_id' => $proposal->group_id,
                'title' => $proposal->title,
            ])
            ->log('created proposal by :causer.name');

        if ($proposal->group?->section?->instructor) {
            $this->notificationService->sendToGroupAdviser($proposal->group, new ProposalSubmitted($proposal));
        }

        return $proposal->fresh();
    }
}
