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
        $programs = [
            ['name' => 'Computer Science', 'department_id' => 1],
            ['name' => 'Information Technology', 'department_id' => 1],
            ['name' => 'Business Administration', 'department_id' => 5],
            ['name' => 'Marketing', 'department_id' => 5],
            ['name' => 'Educational Psychology', 'department_id' => 2],
            ['name' => 'Communication Studies', 'department_id' => 2],
            ['name' => 'Criminal Justice', 'department_id' => 3],
            ['name' => 'Hotel and Restaurant Management', 'department_id' => 4],
            ['name' => 'Tourism Management', 'department_id' => 4],
            ['name' => 'Psychology', 'department_id' => 2],
        ];

        foreach ($programs as $program) {
            \App\Models\Program::create($program);
        }
    }
}
