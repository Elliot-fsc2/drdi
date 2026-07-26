<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Instructors\InstructorResource;
use App\Models\User;
use App\Notifications\SetInitialPassword;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateInstructor extends CreateRecord
{
    protected static string $resource = InstructorResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['email'] ?? null)) {
            $data['email'] = Str::of($data['first_name'])
                ->lower()
                ->append('.')
                ->append(Str::lower($data['last_name']))
                ->append('@drdi.edu')
                ->toString();
        }

        return $data;
    }

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

        $token = Password::broker()->createToken($user);

        $user->notify(new SetInitialPassword($token));

        return $instructor;
    }
}
