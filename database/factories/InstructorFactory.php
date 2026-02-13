<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\User;
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
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'department_id' => \App\Models\Department::inRandomOrder()->first()->id,
            'role' => fake()->randomElement(\App\Enums\InstructorRole::cases()),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Instructor $instructor) {
            User::factory()->create([
                'name' => $instructor->first_name.' '.$instructor->last_name,
                'email' => strtolower(trim($instructor->first_name)).'.'.strtolower(trim($instructor->last_name)).'@instructor.edu',
                'password' => 'password',
                'profileable_id' => $instructor->id,
                'profileable_type' => Instructor::class,
            ]);
        });
    }
}
