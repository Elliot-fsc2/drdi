<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'is_admin' => true,
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
