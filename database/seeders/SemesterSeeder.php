<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Semester::create([
            'name' => '2nd Semester 2025/2026',
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-15',
        ]);
    }
}
