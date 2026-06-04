<?php

use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Component;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Concerns\InteractsWithBoard;
use Relaticle\Flowforge\Contracts\HasBoard;

new class extends Component implements HasActions, HasBoard, HasForms
{
    use InteractsWithActions;
    use InteractsWithBoard {
        InteractsWithBoard::getDefaultActionRecord insteadof InteractsWithActions;
    }
    use InteractsWithForms;

    public function board(Board $board): Board
    {
        return $board
            ->query(Task::query()->with('user'))
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Assignee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(TaskStatus::class),
            ])
            ->searchable(['title'])
            ->columns(
                array_map(fn (TaskStatus $status) => Column::enum($status), TaskStatus::cases()),
            )
            ->columnActions([
                CreateAction::make()
                    ->label(fn (array $arguments): string => match ($arguments['column'] ?? '') {
                        'pending' => 'Add Pending',
                        'in_progress' => 'Add In Progress',
                        'completed' => 'Add Completed',
                        default => 'Add Task',
                    })
                    ->modalWidth('md')
                    ->modalCloseButton(false)
                    ->createAnother(false)
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->color('info')
                    ->model(Task::class)
                    ->form([
                        TextInput::make('title')->required()->maxLength(255),
                    ])
                    ->mutateDataUsing(function (array $data, array $arguments): array {
                        $data['status'] = $arguments['column'] ?? TaskStatus::PENDING->value;
                        $data['user_id'] = auth()->id();
                        $data['position'] = $this->getBoardPositionInColumn($arguments['column']);

                        return $data;
                    }),
            ])
            ->cardActions([
                EditAction::make()
                    ->model(Task::class)
                    ->color('info')
                    ->modalWidth('md')
                    ->modalCloseButton(false)
                    ->form([
                        TextInput::make('title')->required()->maxLength(255),
                        Select::make('status')
                            ->options(TaskStatus::class)
                            ->required(),
                    ]),
                DeleteAction::make()
                    ->modalCloseButton(false)
                    ->model(Task::class),
            ]);
    }

    public function addTaskAction(): Action
    {
        return Action::make('addTask')
            ->label('Add Task')
            ->color('info')
            ->modalCloseButton(false)
            ->icon('heroicon-m-plus')
            ->modalHeading('Create a new task')
            ->modalWidth('md')
            ->form([
                TextInput::make('title')
                    ->label('Task Title')
                    ->required()
                    ->maxLength(255),
            ])
            ->action(function (array $data): void {
                Task::create([
                    'title' => $data['title'],
                    'status' => TaskStatus::PENDING,
                    'user_id' => auth()->id(),
                    'position' => $this->getBoardPositionInColumn(TaskStatus::PENDING->value),
                ]);

                Notification::make()
                    ->title('Task created successfully.')
                    ->success()
                    ->send();
            });
    }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament/admin/theme.css') }}">
@endassets

<div>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Task Board</h1>
    {{ $this->addTaskAction }}
  </div>
  {{ $this->board }}
</div>
