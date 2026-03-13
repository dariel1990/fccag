<?php

namespace Database\Seeders;

use App\Models\Classification;
use Illuminate\Database\Seeder;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            [
                'name' => 'Member',
                'code' => 'MBR',
                'description' => 'Baptized and committed members of the church.',
            ],
            [
                'name' => 'SI',
                'code' => 'SI',
                'description' => 'Sunday Integrated — regular attendees undergoing integration into membership.',
            ],
            [
                'name' => 'CDC',
                'code' => 'CDC',
                'description' => 'Christian Discipleship Course — participants enrolled in discipleship training.',
            ],
        ];

        foreach ($classifications as $classification) {
            Classification::create($classification);
        }
    }
}
