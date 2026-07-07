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
        $now = now();
        $departments = [
            ['name' => 'CSD', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'EdPsycomm', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Criminal Justice', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HRTM', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Business Administration', 'created_at' => $now, 'updated_at' => $now],
        ];

        \App\Models\Department::insert($departments);
    }
}
