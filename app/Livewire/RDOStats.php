<?php

namespace App\Livewire;

use App\Models\GroupFee;
use App\Models\Instructor;
use App\Models\Section;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class RDOStats extends StatsOverviewWidget
{
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'xl' => 3,
        ];
    }

    protected function getStats(): array
    {
        $data = Cache::flexible('rdo_stats', [300, 900], function (): array {
            $total_collectibles = GroupFee::active()->get()->totalCollectibles();
            $total_expenses = GroupFee::active()->get()->totalExpenses();
            $total_savings = max(GroupFee::active()->get()->totalSavings() ?? 0, 0);

            return [
                'total_instructors' => Instructor::count(),
                'total_students' => Student::count(),
                'total_classes' => Section::active()->count(),
                'total_collectibles' => $total_collectibles,
                'total_expenses' => $total_expenses,
                'total_savings' => $total_savings,
            ];
        });

        return [
            Stat::make('Total Instructors', $data['total_instructors'])
                ->color('success'),
            Stat::make('Total Students', $data['total_students'])
                ->color('danger'),
            Stat::make('Active Classes', $data['total_classes'])
                ->color('primary'),
            Stat::make('Total Collectibles', $data['total_collectibles'])
                ->description('total amount collected this semester')
                ->color('success'),
            Stat::make('Total Expenses', $data['total_expenses'])
                ->description('total amount spent this semester')
                ->color('danger'),
            Stat::make('Total Savings', $data['total_savings'])
                ->description('total amount saved this semester')
                ->color('warning'),
        ];
    }
}
