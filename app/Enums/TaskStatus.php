<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending ',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED => 'green',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-queue-list',
            self::IN_PROGRESS => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
        };
    }
}
