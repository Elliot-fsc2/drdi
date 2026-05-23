<?php

namespace App\Filament\Resources\Instructors\Schemas;

use App\Enums\InstructorRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                Select::make('role')
                    ->options(InstructorRole::class)
                    ->default('instructor')
                    ->required(),
            ]);
    }
}
