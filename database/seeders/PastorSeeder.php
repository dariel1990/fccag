<?php

namespace Database\Seeders;

use App\Models\Pastor;
use Illuminate\Database\Seeder;

class PastorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pastor::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Ptr.',
            'role' => 'Senior Pastor',
            'bio' => 'Senior Pastor of FCCAG with over 20 years of dedicated ministry.',
            'is_active' => true,
        ]);
    }
}
