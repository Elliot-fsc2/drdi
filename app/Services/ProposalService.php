<?php

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Group;
use App\Models\Proposal;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ApproveProposal;
use Illuminate\Support\Facades\Gate;

class ProposalService
{
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

        Gate::authorize('create_proposals');

        $proposal->update($data);

        return $proposal->fresh();
    }

    public function approve(Proposal $proposal, ?string $feedback = null)
    {
        Gate::authorize('approve_proposals');
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

        /** @var \Illuminate\Database\Eloquent\Collection<int, User> $recipients */
        $recipients = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $memberIds)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new ApproveProposal($proposal));
        }
    }

    public function reject(Proposal $proposal, ?string $feedback = null)
    {
        Gate::authorize('reject_proposals');
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

        /** @var \Illuminate\Database\Eloquent\Collection<int, User> $recipients */
        $recipients = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $memberIds)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\RejectProposal($proposal));
        }
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

        Gate::authorize('create_proposals');

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

        return $proposal->fresh();
    }
}
