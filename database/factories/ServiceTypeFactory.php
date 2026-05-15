<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'day_of_week' => fake()->optional(0.8)->randomElement([0, 1, 2, 3, 4, 5, 6]),
            'color' => fake()->optional(0.6)->hexColor(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
