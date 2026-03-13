<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Christian Education Department',
            'Youth Department',
            "Men's Ministry Department",
            "Women's Ministry Department",
        ];

        foreach ($departments as $name) {
            Department::create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }
}
