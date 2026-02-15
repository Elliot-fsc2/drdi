<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ThesisRatesType: string implements HasLabel
{
    case FIXED_PER_GROUP = 'fixed_per_group';
    case PER_PERSONNEL = 'per_personnel';

    public function getLabel(): string
    {
        return match ($this) {
            self::FIXED_PER_GROUP => 'Fixed per Group',
            self::PER_PERSONNEL => 'Per Personnel',
        };
    }

    
}
