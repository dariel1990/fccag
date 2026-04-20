<?php

namespace Database\Factories;

use App\Enums\SiAttendanceStatus;
use App\Models\SiActivity;
use App\Models\SiMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiAttendance>
 */
class SiAttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'si_activity_id' => SiActivity::factory(),
            'si_member_id' => SiMember::factory(),
            'status' => fake()->randomElement(SiAttendanceStatus::cases()),
            'remarks' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => ['status' => SiAttendanceStatus::Present]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => ['status' => SiAttendanceStatus::Absent]);
    }
}
