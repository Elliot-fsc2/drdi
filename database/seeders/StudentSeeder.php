<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production') || Student::exists()) {
            return;
        }

        $students = Student::factory()->count(500)->create();

        $password = Hash::make('password');

        foreach ($students as $student) {
            $email = strtolower($student->first_name).'.'.strtolower($student->last_name).'@student.edu';

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $student->first_name.' '.$student->last_name,
                    'password' => $password,
                    'profileable_id' => $student->id,
                    'profileable_type' => Student::class,
                ]
            );
        }
    }
}
