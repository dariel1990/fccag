<?php

namespace Database\Seeders;

use App\Models\Ministry;
use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ministries = [
            'Worship',
            'Youth',
            'Media',
            'Ushering',
            'Hospitality',
            'Prayer',
            'Evangelism',
        ];

        foreach ($ministries as $name) {
            Ministry::create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }
}
