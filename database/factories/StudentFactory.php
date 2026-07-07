<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $programIds = null;

        if ($programIds === null) {
            $programIds = \App\Models\Program::pluck('id')->all();
        }

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'student_number' => fake()->unique()->numerify(date('Y').'-#####'),
            'program_id' => fake()->randomElement($programIds),
        ];
    }
}
