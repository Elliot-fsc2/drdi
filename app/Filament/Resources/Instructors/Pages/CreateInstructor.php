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

        $password = $data['password'] ?? Str::random(12);

        $user = User::create([
            'name' => $instructor->first_name.' '.$instructor->last_name,
            'email' => $data['email'],
            'password' => $password,
        ]);

        $user->profileable()->associate($instructor);
        $user->save();

        if ($data['send_email']) {
            $user->notify(new SendWelcomeEmail($password));
        }

        return $instructor;
    }
}
