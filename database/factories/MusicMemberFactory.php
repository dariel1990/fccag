<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MusicMember>
 */
class MusicMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'user_id' => null,
            'instruments' => fake()->optional(0.6)->randomElement(['Guitar', 'Bass', 'Keys', 'Drums', 'Vocals']),
            'is_active' => true,
        ];
    }
}
