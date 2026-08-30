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
            ['name' => 'Computer Science', 'department' => 'CSD'],
            ['name' => 'Information Technology', 'department' => 'CSD'],
            ['name' => 'Business Administration', 'department' => 'Business Administration'],
            ['name' => 'Marketing', 'department' => 'Business Administration'],
            ['name' => 'Educational Psychology', 'department' => 'EdPsycomm'],
            ['name' => 'Communication Studies', 'department' => 'EdPsycomm'],
            ['name' => 'Criminal Justice', 'department' => 'Criminal Justice'],
            ['name' => 'Hotel and Restaurant Management', 'department' => 'HRTM'],
            ['name' => 'Tourism Management', 'department' => 'HRTM'],
            ['name' => 'Psychology', 'department' => 'EdPsycomm'],
        ];

        $departments = \App\Models\Department::pluck('id', 'name');

        foreach ($programs as $program) {
            $departmentId = $departments[$program['department']] ?? null;

            if ($departmentId === null) {
                throw new \RuntimeException("Department '{$program['department']}' not found for program '{$program['name']}'.");
            }

            \App\Models\Program::firstOrCreate(
                ['name' => $program['name']],
                ['department_id' => $departmentId],
            );
        }
    }
}
