<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PostType: string implements HasLabel
{
    case INSTRUCTORS = 'instructors';
    case STUDENTS = 'students';
    case SECTIONS = 'sections';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::INSTRUCTORS => 'Instructors',
            self::STUDENTS => 'Students',
            self::SECTIONS => 'Sections',
        };
    }
}
