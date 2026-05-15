<?php

namespace Database\Seeders;

use App\Models\WordPack;
use Illuminate\Database\Seeder;

class WordPackSeeder extends Seeder
{
    public function run(): void
    {
        $packs = [
            'Animals' => ['Dog', 'Cat', 'Elephant', 'Tiger', 'Eagle', 'Dolphin', 'Snake', 'Rabbit', 'Horse', 'Penguin'],
            'Food' => ['Pizza', 'Sushi', 'Burger', 'Tacos', 'Ramen', 'Pasta', 'Steak', 'Salad', 'Curry', 'Dumpling'],
            'Countries' => ['Japan', 'Brazil', 'France', 'Egypt', 'Canada', 'India', 'Mexico', 'Italy', 'Kenya', 'Australia'],
        ];

        foreach ($packs as $category => $words) {
            foreach ($words as $word) {
                WordPack::updateOrCreate(
                    ['category' => $category, 'word' => $word],
                );
            }
        }
    }
}
