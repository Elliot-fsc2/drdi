<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Instructors\InstructorResource;
use App\Models\User;
use App\Notifications\SendWelcomeEmail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateInstructor extends CreateRecord
{
    protected static string $resource = InstructorResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $instructor = static::getModel()::create($data);

        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $instructor->first_name.' '.$instructor->last_name,
            'email' => Str::of($instructor->first_name)
                ->trim()
                ->lower()
                ->append('.')
                ->append(Str::of($instructor->last_name)->trim()->lower())
                ->append('@instructor.edu')
                ->toString(),
            'password' => $temporaryPassword,
        ]);

        $user->profileable()->associate($instructor);
        $user->save();

        $user->notify(new SendWelcomeEmail($temporaryPassword));

        return $instructor;
    }
}
