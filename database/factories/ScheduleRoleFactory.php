<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleRole>
 */
class ScheduleRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team' => fake()->randomElement(['band', 'media', 'worship']),
            'name' => fake()->unique()->word(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
