<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use App\Notifications\SendWelcomeEmail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['email'] ?? null)) {
            $data['email'] = Str::of($data['first_name'])
                ->trim()
                ->lower()
                ->append('.')
                ->append(Str::of($data['last_name'])->trim()->lower())
                ->append('@student.edu')
                ->toString();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $student = static::getModel()::create($data);

        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $student->first_name.' '.$student->last_name,
            'email' => $data['email'],
            'password' => $temporaryPassword,
        ]);

        $user->profileable()->associate($student);
        $user->save();

        $user->notify(new SendWelcomeEmail($temporaryPassword));

        return $student;
    }
}
