<?php

namespace App\Livewire;

use App\Models\GroupFee;
use App\Models\Instructor;
use App\Models\Section;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

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
        $total_instructors = Instructor::count();
        $total_students = Student::count();
        $total_classes = Section::active()->count();
        $total_collectibles = GroupFee::active()->get()->totalCollectibles();
        $total_expenses = GroupFee::active()->get()->totalExpenses();
        $total_savings = GroupFee::active()->get()->totalSavings();

        return [
            Stat::make('Total Instructors', $total_instructors)
                ->color('success'),
            Stat::make('Total Students', $total_students)
                ->color('danger'),
            Stat::make('Active Classes', $total_classes)
                ->color('primary'),
            Stat::make('Total Collectibles', $total_collectibles)
                ->description('total amount collected this semester')
                ->color('success'),
            Stat::make('Total Expenses', $total_expenses)
                ->description('total amount spent this semester')
                ->color('danger'),
            Stat::make('Total Savings', $total_savings)
                ->description('total amount saved this semester')
                ->color('warning'),
        ];
    }
}
