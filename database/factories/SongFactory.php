<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Song>
 */
class SongFactory extends Factory
{
    public function definition(): array
    {
        $keys = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

        return [
            'created_by' => User::factory(),
            'title' => fake()->sentence(3),
            'artist' => fake()->optional(0.7)->name(),
            'original_key' => fake()->randomElement($keys),
            'tempo' => fake()->optional(0.6)->numberBetween(60, 160),
            'time_signature' => fake()->optional(0.8)->randomElement(['4/4', '3/4', '6/8']),
            'lyrics_with_chords' => "[G]Amazing [Em]grace how [C]sweet the [G]sound\n[G]That saved a [Em]wretch like [D]me",
            'notes' => fake()->optional(0.3)->sentence(),
            'is_active' => true,
        ];
    }
}
