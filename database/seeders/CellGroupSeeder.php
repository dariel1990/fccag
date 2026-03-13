<?php

namespace Database\Seeders;

use App\Models\CellGroup;
use Illuminate\Database\Seeder;

class CellGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo'];

        foreach ($groups as $name) {
            CellGroup::create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }
}
