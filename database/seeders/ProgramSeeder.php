<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $programs = [
            ['name' => 'Computer Science', 'department_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Information Technology', 'department_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Business Administration', 'department_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Marketing', 'department_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Educational Psychology', 'department_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Communication Studies', 'department_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Criminal Justice', 'department_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hotel and Restaurant Management', 'department_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tourism Management', 'department_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Psychology', 'department_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];

        \App\Models\Program::insert($programs);
    }
}
