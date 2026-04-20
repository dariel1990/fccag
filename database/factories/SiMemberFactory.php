<?php

namespace Database\Factories;

use App\Enums\SiMemberSex;
use App\Enums\SiMemberStatus;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiMember>
 */
class SiMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'caregiver_id' => Participant::factory(),
            'name' => fake()->name(),
            'sex' => fake()->randomElement(SiMemberSex::cases()),
            'ph_id' => fake()->optional(0.8)->numerify('###'),
            'address' => fake()->address(),
            'status' => SiMemberStatus::Active,
            'enrolled_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'exited_at' => null,
        ];
    }

    public function exited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SiMemberStatus::Exit,
            'exited_at' => fake()->dateTimeBetween($attributes['enrolled_at'], 'now'),
        ]);
    }
}
