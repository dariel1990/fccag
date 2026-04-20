<?php

namespace Database\Factories;

use App\Models\SiActivityCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiActivity>
 */
class SiActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'si_activity_category_id' => SiActivityCategory::factory(),
            'activity_id' => null,
            'title' => fake()->sentence(3),
            'speaker' => fake()->optional(0.8)->name(),
            'topic' => fake()->optional(0.8)->sentence(4),
            'memory_verse' => fake()->optional(0.7)->sentence(6),
            'conducted_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
