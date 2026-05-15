<?php

namespace Database\Factories;

use App\Models\MusicMember;
use App\Models\Schedule;
use App\Models\ScheduleRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleAssignment>
 */
class ScheduleAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'schedule_role_id' => ScheduleRole::factory(),
            'music_member_id' => MusicMember::factory(),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
