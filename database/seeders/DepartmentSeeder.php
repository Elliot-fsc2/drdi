<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'CSD',
            'EdPsycomm',
            'Criminal Justice',
            'HRTM',
            'Business Administration',
        ];

        foreach ($departments as $department) {
            \App\Models\Department::create(['name' => $department]);
        }
    }
}
