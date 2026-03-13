<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Christian Education Department',
                'Youth Department',
                "Men's Ministry Department",
                "Women's Ministry Department",
            ]),
            'description' => fake()->optional(0.7)->sentence(),
            'photo_path' => null,
            'is_active' => true,
        ];
    }
}
