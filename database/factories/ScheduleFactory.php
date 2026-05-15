<?php

namespace Database\Factories;

use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_type_id' => ServiceType::factory(),
            'service_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'status' => 'active',
            'notes' => fake()->optional(0.3)->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
