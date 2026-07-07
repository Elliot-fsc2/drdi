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
        $students = Student::factory()->count(500)->create();

        $now = now();
        $password = Hash::make('password');

        $users = $students->map(fn (Student $student) => [
            'name' => $student->first_name.' '.$student->last_name,
            'email' => strtolower($student->first_name).'.'.strtolower($student->last_name).'@student.edu',
            'password' => $password,
            'profileable_id' => $student->id,
            'profileable_type' => Student::class,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        User::insert($users);
    }
}
