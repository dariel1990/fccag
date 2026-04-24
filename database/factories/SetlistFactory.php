<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setlist>
 */
class SetlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => fake()->words(3, true),
            'service_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'theme' => fake()->optional(0.6)->sentence(4),
            'notes' => fake()->optional(0.4)->paragraph(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'draft']);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'published']);
    }
}
