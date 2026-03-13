<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classification>
 */
class ClassificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Not used directly; ClassificationSeeder uses fixed data
        return [
            'name' => fake()->word(),
            'code' => fake()->unique()->lexify('???'),
            'description' => fake()->sentence(),
        ];
    }
}
