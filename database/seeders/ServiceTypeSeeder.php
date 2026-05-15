<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            ['name' => 'Cottage Service', 'day_of_week' => 4, 'sort_order' => 1],
            ['name' => 'Prayer Meeting', 'day_of_week' => 5, 'sort_order' => 2],
            ['name' => 'Prayer & Fasting', 'day_of_week' => null, 'sort_order' => 3],
            ['name' => 'Divine Service', 'day_of_week' => 0, 'sort_order' => 4],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::updateOrCreate(
                ['name' => $serviceType['name']],
                $serviceType
            );
        }
    }
}
