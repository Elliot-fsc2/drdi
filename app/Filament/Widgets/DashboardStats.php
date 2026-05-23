<?php

namespace App\Filament\Widgets;

use App\Enums\InstructorRole;
use App\Models\Instructor;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 4,
        ];
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Students', Student::query()->count('id'))
                ->color('primary'),
            Stat::make('Instructors', Instructor::query()->count('id'))
                ->color('success'),
            Stat::make('RDO', Instructor::query()->where('role', InstructorRole::RDO)->count('id'))
                ->color('warning'),
            Stat::make('Staff', Instructor::query()->where('role', InstructorRole::Staff)->count('id'))
                ->color('gray'),
        ];
    }
}
