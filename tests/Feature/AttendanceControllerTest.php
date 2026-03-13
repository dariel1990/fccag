<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Attendance;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Activity $activity;

    private Participant $participant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $activityType = ActivityType::factory()->create();
        $this->activity = Activity::factory()->create(['activity_type_id' => $activityType->id]);
        $this->participant = Participant::factory()->create(['is_active' => true]);
    }

    public function test_attendance_can_be_recorded(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.store', $this->activity), [
                'attendances' => [
                    [
                        'person_id' => $this->participant->id,
                        'is_present' => true,
                        'remarks' => 'On time',
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'activity_id' => $this->activity->id,
            'person_id' => $this->participant->id,
            'is_present' => true,
            'remarks' => 'On time',
        ]);
    }

    public function test_attendance_can_be_updated(): void
    {
        Attendance::factory()->create([
            'activity_id' => $this->activity->id,
            'person_id' => $this->participant->id,
            'is_present' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('attendance.store', $this->activity), [
                'attendances' => [
                    [
                        'person_id' => $this->participant->id,
                        'is_present' => true,
                        'remarks' => 'Corrected',
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'activity_id' => $this->activity->id,
            'person_id' => $this->participant->id,
            'is_present' => true,
            'remarks' => 'Corrected',
        ]);
    }

    public function test_bulk_attendance_can_be_recorded(): void
    {
        $participants = Participant::factory(5)->create(['is_active' => true]);

        $attendances = $participants->map(fn ($p) => [
            'person_id' => $p->id,
            'is_present' => true,
            'remarks' => null,
        ])->toArray();

        $response = $this->actingAs($this->user)
            ->post(route('attendance.store', $this->activity), [
                'attendances' => $attendances,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('attendances', 5);
    }

    public function test_attendance_requires_person_id(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.store', $this->activity), [
                'attendances' => [
                    [
                        'person_id' => '',
                        'is_present' => true,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('attendances.0.person_id');
    }

    public function test_attendance_requires_valid_person(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.store', $this->activity), [
                'attendances' => [
                    [
                        'person_id' => 99999,
                        'is_present' => true,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('attendances.0.person_id');
    }
}
