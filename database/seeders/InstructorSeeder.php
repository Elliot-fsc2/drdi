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
        $instructors = Instructor::factory(50)->create();

        $now = now();
        $password = Hash::make('password');

        $users = $instructors->map(fn (Instructor $instructor) => [
            'name' => $instructor->first_name.' '.$instructor->last_name,
            'email' => strtolower($instructor->first_name).'.'.strtolower($instructor->last_name).'@instructor.edu',
            'password' => $password,
            'profileable_id' => $instructor->id,
            'profileable_type' => Instructor::class,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        User::insert($users);
    }
}
