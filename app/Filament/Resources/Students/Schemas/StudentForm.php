<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('student_number')
                    ->required()
                    ->unique(table: 'students', column: 'student_number', ignoreRecord: true),
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
                Select::make('program_id')
                    ->relationship('program', 'name')
                    ->required(),
            ]);
    }
}
