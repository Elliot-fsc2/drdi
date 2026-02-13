<?php

namespace Database\Factories;

use App\Models\Student;
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
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'student_number' => fake()->unique()->numerify(date('Y').'-#####'),
            'program_id' => \App\Models\Program::inRandomOrder()->first()->id,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Student $student) {
            \App\Models\User::factory()->create([
                'name' => $student->first_name.' '.$student->last_name,
                'email' => strtolower(trim($student->first_name)).'.'.strtolower(trim($student->last_name)).'@student.edu',
                'password' => 'password',
                'profileable_id' => $student->id,
                'profileable_type' => Student::class,
            ]);
        });
    }
}
