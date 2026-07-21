<?php

namespace App\Filament\Imports;

use App\Enums\InstructorRole;
use App\Models\Department;
use App\Models\Instructor;
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

class InstructorImporter extends Importer
{
    protected static ?string $model = Instructor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Juan')
                ->helperText('Instructor\'s first name (required).'),
            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Dela Cruz')
                ->helperText('Instructor\'s last name (required).'),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:255'])
                ->fillRecordUsing(function (): void {})
                ->example('juan.delacruz@drdi.edu')
                ->helperText('Email address. Leave blank to auto-generate from name.'),
            ImportColumn::make('department')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(function (): void {})
                ->example('CSD')
                ->helperText('Department name (must match existing department).'),
            ImportColumn::make('role')
                ->rules(['nullable', 'string', 'max:255'])
                ->fillRecordUsing(function (): void {})
                ->example('Instructor')
                ->helperText('Role: Instructor, RDO, or Staff (case-insensitive). Defaults to Instructor.'),

        ];
    }

    public function resolveRecord(): Instructor
    {
        $behavior = $this->options['import_behavior'] ?? 'add_new_and_update';
        $email = $this->data['email'] ?? null;

        if ($behavior === 'add_new') {
            if ($email) {
                $existing = User::where('email', $email)
                    ->where('profileable_type', (new Instructor)->getMorphClass())
                    ->first();

                if ($existing) {
                    throw new RowImportFailedException(
                        "Instructor with email {$email} already exists."
                    );
                }

                if (User::where('email', $email)->where('profileable_type', '!=', (new Instructor)->getMorphClass())->exists()) {
                    throw new RowImportFailedException(
                        "Email {$email} is already taken by another user."
                    );
                }
            }

            return new Instructor;
        }

        $existing = null;
        if ($email) {
            $user = User::where('email', $email)
                ->where('profileable_type', (new Instructor)->getMorphClass())
                ->first();

            $existing = $user?->profileable;
        }

        if ($behavior === 'update_existing') {
            if (! $existing) {
                throw new RowImportFailedException(
                    "No instructor found with email {$email}."
                );
            }

            return $existing;
        }

        if (! $existing && $email && User::where('email', $email)->exists()) {
            throw new RowImportFailedException(
                "Email {$email} is already taken by another user."
            );
        }

        return $existing ?? new Instructor;
    }

    protected function beforeValidate(): void
    {
        if (blank($this->data['email'] ?? null)) {
            $this->data['email'] = Str::of($this->data['first_name'])
                ->lower()
                ->append('.')
                ->append(Str::lower($this->data['last_name']))
                ->append('@drdi.edu')
                ->toString();
        }
    }

    protected function beforeSave(): void
    {
        try {
            if ($departmentName = $this->data['department'] ?? null) {
                $department = Department::where('name', $departmentName)->first();

                if ($department) {
                    $this->record->department_id = $department->id;
                } else {
                    throw new RowImportFailedException(
                        "Department \"{$departmentName}\" not found. Please check the department name."
                    );
                }
            }

            if ($role = $this->data['role'] ?? null) {
                $matched = collect(InstructorRole::cases())
                    ->first(fn (InstructorRole $case) => strcasecmp($case->value, $role) === 0);

                if (! $matched) {
                    throw new RowImportFailedException(
                        "Invalid role \"{$role}\". Valid roles: Instructor, RDO, Staff (case-insensitive)."
                    );
                }

                $this->record->role = $matched;
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
            $password = Str::random(12);
            $sendEmail = $this->options['send_email'] ?? true;

            $user = User::create([
                'name' => $this->record->first_name.' '.$this->record->last_name,
                'email' => $this->data['email'],
                'password' => $password,
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
        $body = 'Your instructor import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
