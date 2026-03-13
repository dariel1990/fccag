<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Attendance;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activityTypes = ActivityType::all();
        $participants = Participant::all();

        foreach ($activityTypes as $activityType) {
            $activitiesCount = rand(4, 8);

            $activities = Activity::factory($activitiesCount)->create([
                'activity_type_id' => $activityType->id,
            ]);

            foreach ($activities as $activity) {
                foreach ($participants as $participant) {
                    Attendance::factory()->create([
                        'activity_id' => $activity->id,
                        'person_id' => $participant->id,
                    ]);
                }
            }
        }
    }
}
