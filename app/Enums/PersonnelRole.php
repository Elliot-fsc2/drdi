<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PersonnelRole: string implements HasLabel
{
    case TECHNICAL_ADVISER = 'technical_adviser';
    case GRAMMARIAN = 'grammarian';
    case LANGUAGE_CRITIC = 'language_critic';
    case STATISTICIAN = 'statistician';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::TECHNICAL_ADVISER => 'Technical Adviser',
            self::GRAMMARIAN => 'Grammarian',
            self::LANGUAGE_CRITIC => 'Language Critic',
            self::STATISTICIAN => 'Statistician',
        };
    }
}
