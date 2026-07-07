<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $departmentIds = null;

        if ($departmentIds === null) {
            $departmentIds = \App\Models\Department::pluck('id')->all();
        }

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'department_id' => fake()->randomElement($departmentIds),
            'role' => fake()->randomElement(\App\Enums\InstructorRole::cases()),
        ];
    }
}
