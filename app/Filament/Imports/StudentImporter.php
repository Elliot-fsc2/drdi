<?php

namespace App\Filament\Imports;

use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Notifications\SetInitialPassword;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Maria')
                ->helperText('Student\'s first name (required).'),
            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Santos')
                ->helperText('Student\'s last name (required).'),
            ImportColumn::make('student_number')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('2020-00001')
                ->helperText('Student number (required, used for matching).'),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:255'])
                ->fillRecordUsing(function (): void {})
                ->example('maria.santos@student.edu')
                ->helperText('Email address. Leave blank to auto-generate from name.'),
            ImportColumn::make('program')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(function (): void {})
                ->example('BSIT')
                ->helperText('Program name (must match existing program).'),
        ];
    }

    public function resolveRecord(): Student
    {
        $behavior = $this->options['import_behavior'] ?? 'add_new_and_update';
        $studentNumber = $this->data['student_number'] ?? null;

        $email = $this->data['email'] ?? null;

        if ($behavior === 'add_new') {
            if ($studentNumber) {
                $existing = Student::where('student_number', $studentNumber)->first();

                if ($existing) {
                    throw new RowImportFailedException(
                        "Student with student number {$studentNumber} already exists."
                    );
                }
            }

            if ($email && User::where('email', $email)->exists()) {
                throw new RowImportFailedException(
                    "Email {$email} is already taken by another user."
                );
            }

            return new Student;
        }

        $existing = $studentNumber
            ? Student::where('student_number', $studentNumber)->first()
            : null;

        if ($behavior === 'update_existing') {
            if (! $existing) {
                throw new RowImportFailedException(
                    "No student found with student number {$studentNumber}."
                );
            }

            return $existing;
        }

        if (! $existing && $email && User::where('email', $email)->exists()) {
            throw new RowImportFailedException(
                "Email {$email} is already taken by another user."
            );
        }

        return $existing ?? new Student;
    }

    protected function beforeValidate(): void
    {
        if (blank($this->data['email'] ?? null)) {
            $this->data['email'] = Str::of($this->data['first_name'])
                ->trim()
                ->lower()
                ->append('.')
                ->append(Str::of($this->data['last_name'])->trim()->lower())
                ->append('@student.edu')
                ->toString();
        }
    }

    protected function beforeSave(): void
    {
        try {
            if ($programName = $this->data['program'] ?? null) {
                $program = Program::where('name', $programName)->first();

                if ($program) {
                    $this->record->program_id = $program->id;
                } else {
                    throw new RowImportFailedException(
                        "Program \"{$programName}\" not found. Please check the program name."
                    );
                }
            }
        } catch (RowImportFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RowImportFailedException($e->getMessage());
        }
    }

    protected function afterCreate(): void
    {
        try {
            $temporaryPassword = Str::random(12);
            $sendEmail = $this->options['send_email'] ?? true;

            $user = User::create([
                'name' => $this->record->first_name.' '.$this->record->last_name,
                'email' => $this->data['email'],
                'password' => $temporaryPassword,
            ]);

            $user->profileable()->associate($this->record);
            $user->save();

            if ($sendEmail) {
                $token = Password::broker()->createToken($user);
                $user->notify(new SetInitialPassword($token));
            }
        } catch (RowImportFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RowImportFailedException($e->getMessage());
        }
    }

    protected function afterUpdate(): void
    {
        try {
            $user = $this->record->user;

            if ($user) {
                $user->update([
                    'name' => $this->record->first_name.' '.$this->record->last_name,
                    'email' => $this->data['email'],
                ]);
            }
        } catch (RowImportFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RowImportFailedException($e->getMessage());
        }
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('import_behavior')
                ->label('What to do with existing records')
                ->options([
                    'add_new' => 'Add new only (skip existing)',
                    'update_existing' => 'Update existing only (skip new)',
                    'add_new_and_update' => 'Add new and update existing',
                ])
                ->default('add_new_and_update')
                ->required(),
            Checkbox::make('send_email')
                ->label('Send password setup link to new users')
                ->default(true),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
