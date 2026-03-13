<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CellGroup>
 */
class CellGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Grace', 'Hope']),
            'leader' => fake()->optional(0.8)->name(),
            'description' => fake()->optional(0.6)->sentence(),
            'is_active' => true,
        ];
    }
}
