<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'person_id' => Participant::factory(),
            'is_present' => fake()->boolean(75),
            'remarks' => fake()->optional(0.1)->sentence(),
        ];
    }

    /**
     * Indicate that the participant was present.
     */
    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_present' => true,
        ]);
    }

    /**
     * Indicate that the participant was absent.
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_present' => false,
        ]);
    }
}
