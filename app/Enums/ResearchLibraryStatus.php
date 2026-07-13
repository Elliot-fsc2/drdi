<?php

namespace App\Enums;

enum ResearchLibraryStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
