<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DepartmentOfficer>
 */
class DepartmentOfficerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'person_id' => Participant::factory(),
            'role' => fake()->randomElement(['President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor']),
            'started_at' => fake()->dateTimeBetween('-2 years', '-6 months'),
            'ended_at' => null,
        ];
    }
}
