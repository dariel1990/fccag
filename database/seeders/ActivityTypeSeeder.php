<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Life Class', 'description' => 'Weekly life class sessions for new members'],
            ['name' => 'Sunday Service', 'description' => 'Regular Sunday worship service'],
            ['name' => 'Prayer Meeting', 'description' => 'Weekly prayer gathering'],
            ['name' => 'Bible Study', 'description' => 'In-depth Bible study sessions'],
            ['name' => 'Youth Fellowship', 'description' => 'Fellowship activities for youth members'],
        ];

        foreach ($types as $type) {
            ActivityType::factory()->create($type);
        }
    }
}
