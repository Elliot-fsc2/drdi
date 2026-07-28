<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PanelistRole: string implements HasLabel
{
    case CHAIRPERSON = 'chairperson';
    case GUEST_PANEL = 'guest_panel';
    case MEMBER = 'member';

    public function getLabel(): string
    {
        return match ($this) {
            self::CHAIRPERSON => 'Chairperson',
            self::GUEST_PANEL => 'Guest Panel',
            self::MEMBER => 'Member',
        };
    }
}
