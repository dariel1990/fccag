<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(6);
        $publishedAt = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->paragraph(2),
            'body' => implode("\n\n", $this->faker->paragraphs(5)),
            'category' => $this->faker->randomElement(['blog', 'news', 'announcement']),
            'is_published' => true,
            'published_at' => $publishedAt,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
