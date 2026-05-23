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

    protected function handleRecordCreation(array $data): Model
    {
        $student = static::getModel()::create($data);

        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $student->first_name.' '.$student->last_name,
            'email' => Str::of($student->first_name)
                ->trim()
                ->lower()
                ->append('.')
                ->append(Str::of($student->last_name)->trim()->lower())
                ->append('@student.edu')
                ->toString(),
            'password' => $temporaryPassword,
        ]);

        $user->profileable()->associate($student);
        $user->save();

        $user->notify(new SendWelcomeEmail($temporaryPassword));

        return $student;
    }
}
