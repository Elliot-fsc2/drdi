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
                    ->required(),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->nullable()
                    ->placeholder('Auto-filled from name if empty'),
                Select::make('program_id')
                    ->relationship('program', 'name')
                    ->required(),
            ]);
    }
}
