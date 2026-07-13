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
        $this->call([
            RolesAndPermissionSeeder::class,
            SemesterSeeder::class,
            DepartmentSeeder::class,
            ProgramSeeder::class,
        ]);

        if (! app()->environment('production')) {
            $this->call([
                InstructorSeeder::class,
                StudentSeeder::class,
            ]);
        }

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => '@Admin2221',
                'is_admin' => true,
            ]
        )->assignRole('super_admin');

        User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher Demo',
                'password' => '@Teacher2221',
                'is_admin' => false,
            ]
        )->assignRole('instructor');

        $instructorUser = User::where('email', 'teacher@example.com')->first();
        if ($instructorUser && ! $instructorUser->profileable_id) {
            $instructor = Instructor::firstOrCreate(
                ['first_name' => 'Teacher', 'last_name' => 'Demo'],
                ['role' => \App\Enums\InstructorRole::Instructor]
            );
            $instructorUser->update([
                'profileable_id' => $instructor->id,
                'profileable_type' => Instructor::class,
            ]);
        }

        User::firstOrCreate(
            ['email' => 'rdo@example.com'],
            [
                'name' => 'RDO Demo',
                'password' => '@Rdo2221',
                'is_admin' => false,
            ]
        )->assignRole('rdo');

        $rdoUser = User::where('email', 'rdo@example.com')->first();
        if ($rdoUser && ! $rdoUser->profileable_id) {
            $rdoInstructor = Instructor::firstOrCreate(
                ['first_name' => 'RDO', 'last_name' => 'Demo'],
                ['role' => \App\Enums\InstructorRole::RDO]
            );
            $rdoUser->update([
                'profileable_id' => $rdoInstructor->id,
                'profileable_type' => Instructor::class,
            ]);
        }

        User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student Demo',
                'password' => '@Student2221',
                'is_admin' => false,
            ]
        )->assignRole('student');

        $studentUser = User::where('email', 'student@example.com')->first();
        if ($studentUser && ! $studentUser->profileable_id) {
            $student = Student::firstOrCreate(
                ['first_name' => 'Student', 'last_name' => 'Demo'],
                ['student_number' => 'DEMO-00001']
            );
            $studentUser->update([
                'profileable_id' => $student->id,
                'profileable_type' => Student::class,
            ]);
        }
    }
}
