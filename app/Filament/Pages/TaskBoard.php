<?php

namespace App\Filament\Pages;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardPage;
use Relaticle\Flowforge\Column;

class TaskBoard extends BoardPage
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Task Board';

    protected static ?string $title = 'Task Board';

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getEloquentQuery())
            ->recordTitleAttribute('title')
            ->columnIdentifier('status')
            ->headerToolbar()
            ->searchable(['title'])
            ->positionIdentifier('position')
            ->columns([
                Column::make('pending')->label('To Do')->color('gray')
                    ->icon('heroicon-o-queue-list'),
                Column::make('in_progress')->label('In Progress')->color('blue'),
                Column::make('completed')->label('Completed')->color('green'),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        return Task::query();
    }
}
