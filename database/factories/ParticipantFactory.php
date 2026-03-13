<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\CellGroup;
use App\Models\Classification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());

        return [
            'first_name' => fake()->firstName($gender === Gender::Male ? 'male' : 'female'),
            'last_name' => fake()->lastName(),
            'gender' => $gender,
            'birthday' => fake()->dateTimeBetween('-60 years', '-15 years'),
            'contact_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'cell_group_id' => fake()->optional(0.7)->passthrough(CellGroup::inRandomOrder()->value('id')),
            'classification_id' => fake()->optional(0.9)->passthrough(Classification::inRandomOrder()->value('id')),
            'date_joined' => fake()->dateTimeBetween('-2 years', 'now'),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the participant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
