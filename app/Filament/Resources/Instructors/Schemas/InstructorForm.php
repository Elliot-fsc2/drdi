<?php

namespace App\Filament\Resources\Instructors\Schemas;

use App\Enums\InstructorRole;
use App\Models\Department;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class InstructorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->nullable()
                    ->placeholder('Auto-filled from name if empty')
                    ->unique(table: 'users', column: 'email', ignoreRecord: true,
                        modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, $livewire) {
                            $user = $livewire?->record?->user;

                            return $rule->ignore($user?->getKey() ?? $livewire?->record?->getKey(), 'id');
                        },
                    ),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->helperText('Leave empty to auto-generate a password.'),
                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->same('password'),
                    ]),
                Select::make('department_id')
                    ->options(Department::pluck('name', 'id'))
                    ->nullable(),
                Select::make('role')
                    ->options(InstructorRole::class)
                    ->default('instructor')
                    ->required(),
                Checkbox::make('send_email')
                    ->label('Send welcome email with account details')
                    ->default(true),
            ]);
    }
}
