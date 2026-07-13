<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production') || Instructor::exists()) {
            return;
        }

        $instructors = Instructor::factory(50)->create();

        $password = Hash::make('password');

        foreach ($instructors as $instructor) {
            $email = strtolower($instructor->first_name).'.'.strtolower($instructor->last_name).'@instructor.edu';

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $instructor->first_name.' '.$instructor->last_name,
                    'password' => $password,
                    'profileable_id' => $instructor->id,
                    'profileable_type' => Instructor::class,
                ]
            );
        }
    }
}
