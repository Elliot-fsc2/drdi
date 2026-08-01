<?php

namespace App\Enums;

enum NotificationType: string
{
    case GROUP_CREATED = 'group_created';
    case GROUP_UPDATED = 'group_updated';
    case GROUP_DELETED = 'group_deleted';
    case MEMBER_ADDED = 'member_added';
    case MEMBER_REMOVED = 'member_removed';
    case PERSONNEL_ASSIGNED = 'personnel_assigned';
    case PROPOSAL_SUBMITTED = 'proposal_submitted';
    case PROPOSAL_APPROVED = 'proposal_approved';
    case PROPOSAL_REJECTED = 'proposal_rejected';
    case SCHEDULE_CREATED = 'schedule_created';
    case SCHEDULE_UPDATED = 'schedule_updated';
    case SCHEDULE_RESULT = 'schedule_result';
    case CONSULTATION_BOOKED = 'consultation_booked';
    case CONSULTATION_UPDATED = 'consultation_updated';
    case RESEARCH_SUBMITTED = 'research_submitted';
    case RESEARCH_APPROVED = 'research_approved';
    case RESEARCH_REJECTED = 'research_rejected';
    case RESEARCH_PUBLISHED = 'research_published';
    case FEE_LEDGER_INITIALIZED = 'fee_ledger_initialized';
    case FEE_UPDATED = 'fee_updated';
    case NEW_ANNOUNCEMENT = 'new_announcement';
    case SEMESTER_RATE_UPDATED = 'semester_rate_updated';
    case GROUP_STATUS_CHANGED = 'group_status_changed';
    case WELCOME = 'welcome';
}
