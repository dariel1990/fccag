<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pastor>
 */
class PastorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName('male'),
            'last_name' => fake()->lastName(),
            'title' => fake()->randomElement(['Ptr.', 'Rev.', 'Bro.']),
            'role' => fake()->randomElement(['Senior Pastor', 'Associate Pastor', 'Youth Pastor']),
            'bio' => fake()->optional(0.8)->paragraph(),
            'photo_path' => null,
            'contact_number' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'date_started' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'is_active' => true,
        ];
    }
}
