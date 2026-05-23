<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLog extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.activity-log';

    protected static ?string $title = 'Activity Log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

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
            ->filters([
                SelectFilter::make('log_name')
                    ->options(fn (): array => $this->activityLogOptions('log_name')),
                SelectFilter::make('event')
                    ->options(fn (): array => $this->activityLogOptions('event')),
                SelectFilter::make('subject_type')
                    ->label('Subject Type')
                    ->options(fn (): array => $this->activityLogOptions('subject_type', true)),
                SelectFilter::make('causer_type')
                    ->label('Causer Type')
                    ->options(fn (): array => $this->activityLogOptions('causer_type', true)),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function activityLogOptions(string $column, bool $useClassBasename = false): array
    {
        return Activity::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column, $column)
            ->mapWithKeys(function (mixed $value) use ($useClassBasename): array {
                $value = (string) $value;

                return [
                    $value => $useClassBasename ? Str::afterLast($value, '\\') : $value,
                ];
            })
            ->all();
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
