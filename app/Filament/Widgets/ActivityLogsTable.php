<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class ActivityLogsTable extends TableWidget
{
    protected static ?string $heading = 'Activity Logs';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->with(['causer', 'subject']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('log_name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->state(fn (Activity $record): string => $this->formatSubject($record))
                    ->wrap(),
                TextColumn::make('causer')
                    ->label('Causer')
                    ->state(fn (Activity $record): string => $this->formatCauser($record))
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }

    private function formatSubject(Activity $record): string
    {
        $subject = $record->subject;

        if ($subject === null) {
            return class_basename((string) $record->subject_type).' #'.$record->subject_id;
        }

        return $subject->name
            ?? $subject->full_name
            ?? $subject->title
            ?? class_basename($record->subject_type ?? '').' #'.$record->subject_id;
    }

    private function formatCauser(Activity $record): string
    {
        $causer = $record->causer;

        if ($causer === null) {
            return 'System';
        }

        return $causer->name
            ?? $causer->full_name
            ?? $causer->title
            ?? class_basename($record->causer_type ?? '').' #'.$record->causer_id;
    }
}
