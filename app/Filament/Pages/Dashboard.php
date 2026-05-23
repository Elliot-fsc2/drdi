<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityLogsTable;
use App\Filament\Widgets\DashboardStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            DashboardStats::class,
            ActivityLogsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
