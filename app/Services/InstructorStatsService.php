<?php

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Proposal;

class InstructorStatsService
{
  public function dashboardStats()
  {
    $user = auth()->user()->profileable;

    $classes = $user->classes()->active()->count();
    $total_students = $user->classes()->active()->withCount('students')->get()->sum('students_count');
    $total_groups = $user->classes()->active()->withCount('groups')->get()->sum('groups_count');

    $activeSectionIds = $user->classes()->active()->pluck('id');
    $proposals = Proposal::where('status', ProposalStatus::PENDING->value)
      ->whereHas('group', fn($q) => $q->whereIn('section_id', $activeSectionIds))
      ->count();

    $consultations = $user->consultations()->where('status', 'scheduled')->with('group')->orderBy('scheduled_at')->get();
    $recent_proposals = Proposal::whereHas('group.section', fn($q) => $q->where('instructor_id', $user->id))
      ->with('group')
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
  }
}
