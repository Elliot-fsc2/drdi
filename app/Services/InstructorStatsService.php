<?php

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class InstructorStatsService
{
    public function dashboardStats(): array
    {
        Gate::authorize('view_dashboard');

        $user = auth()->user()->profileable;

        return Cache::flexible('instructor_dashboard_'.$user->id, [300, 900], function () use ($user): array {
            $classes = $user->classes()->active()->count();
            $total_students = $user->classes()->active()->withCount('students')->get()->sum('students_count');
            $total_groups = $user->classes()->active()->withCount('groups')->get()->sum('groups_count');

            $activeSectionIds = $user->classes()->active()->pluck('id');
            $proposals = Proposal::where('status', ProposalStatus::PENDING->value)
                ->whereHas('group', fn ($q) => $q->whereIn('section_id', $activeSectionIds))
                ->count();

            $consultations = $user->consultations()
                ->where('status', 'scheduled')
                ->with('group.section')
                ->orderBy('scheduled_at')
                ->get();

            $recent_proposals = Proposal::whereHas('group.section', fn ($q) => $q->where('instructor_id', $user->id))
                ->with('group.section')
                ->latest()
                ->take(5)
                ->get();

            return [
                'active_classes' => $classes,
                'total_students' => $total_students,
                'total_groups' => $total_groups,
                'proposals' => $proposals,
                'consultations' => $consultations,
                'recent_proposals' => $recent_proposals,
            ];
        });
    }
}
