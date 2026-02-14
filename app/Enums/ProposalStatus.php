<?php

namespace App\Enums\Enums;

enum ProposalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
