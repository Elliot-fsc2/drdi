<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => '@Admin2221',
            'is_admin' => true,
        ]);

        $instructor = \App\Models\Instructor::factory()->create([
            'first_name' => 'Teacher',
            'last_name' => 'Demo',
        ]);

        User::factory()->create([
            'name' => "$instructor->first_name $instructor->last_name",
            'email' => 'teacher@example.com',
            'password' => '@Teacher2221',
            'is_admin' => false,
            'profileable_id' => $instructor->id,
            'profileable_type' => Instructor::class,
        ]);

        $student = Student::factory()->create([
            'first_name' => 'Student',
            'last_name' => 'Demo',
        ]);

        User::factory()->create([
            'name' => "$student->first_name $student->last_name",
            'email' => 'student@example.com',
            'password' => '@Student2221',
            'is_admin' => false,
            'profileable_id' => $student->id,
            'profileable_type' => Student::class,
        ]);

        $this->call([
            SemesterSeeder::class,
            DepartmentSeeder::class,
            ProgramSeeder::class,
            InstructorSeeder::class,
            StudentSeeder::class,
        ]);
    }
}
