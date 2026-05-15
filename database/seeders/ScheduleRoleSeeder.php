<?php

namespace Database\Seeders;

use App\Models\ScheduleRole;
use Illuminate\Database\Seeder;

class ScheduleRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['team' => 'band', 'name' => 'Acoustic', 'sort_order' => 1],
            ['team' => 'band', 'name' => 'Cajon', 'sort_order' => 2],
            ['team' => 'band', 'name' => 'Keys', 'sort_order' => 3],
            ['team' => 'band', 'name' => 'E. Guitar', 'sort_order' => 4],
            ['team' => 'band', 'name' => 'A. Guitar', 'sort_order' => 5],
            ['team' => 'band', 'name' => 'Bass', 'sort_order' => 6],
            ['team' => 'band', 'name' => 'Drums', 'sort_order' => 7],
            ['team' => 'media', 'name' => 'Media', 'sort_order' => 1],
            ['team' => 'worship', 'name' => 'WL', 'sort_order' => 1],
            ['team' => 'worship', 'name' => 'Backups', 'sort_order' => 2],
        ];

        foreach ($roles as $role) {
            ScheduleRole::updateOrCreate(
                ['team' => $role['team'], 'name' => $role['name']],
                $role
            );
        }
    }
}
