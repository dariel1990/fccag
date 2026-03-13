<?php

namespace Database\Factories;

use App\Models\ActivityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_type_id' => ActivityType::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'activity_date' => fake()->dateTimeBetween('-6 months', '+1 month'),
        ];
    }
}
