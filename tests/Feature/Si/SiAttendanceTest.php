<?php

namespace Tests\Feature\Si;

use App\Enums\SiAttendanceStatus;
use App\Models\SiActivity;
use App\Models\SiAttendance;
use App\Models\SiMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withFullAccess()->create();
    }

    public function test_attendance_can_be_saved_for_assigned_members(): void
    {
        $activity = SiActivity::factory()->create();
        $members = SiMember::factory(3)->create();
        $activity->assignedMembers()->sync($members->pluck('id')->toArray());

        $attendances = $members->map(fn ($m, $i) => [
            'si_member_id' => $m->id,
            'status' => $i === 0 ? SiAttendanceStatus::Present->value : SiAttendanceStatus::Absent->value,
            'remarks' => null,
        ])->toArray();

        $response = $this->actingAs($this->user)
            ->post(route('si.attendance.store', $activity), [
                'attendances' => $attendances,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('si_attendances', 3);
        $this->assertDatabaseHas('si_attendances', [
            'si_activity_id' => $activity->id,
            'si_member_id' => $members->first()->id,
            'status' => SiAttendanceStatus::Present->value,
        ]);
    }

    public function test_attendance_can_be_updated_via_upsert(): void
    {
        $activity = SiActivity::factory()->create();
        $member = SiMember::factory()->create();
        $activity->assignedMembers()->attach($member->id);

        SiAttendance::factory()->create([
            'si_activity_id' => $activity->id,
            'si_member_id' => $member->id,
            'status' => SiAttendanceStatus::Absent,
        ]);

        $this->actingAs($this->user)
            ->post(route('si.attendance.store', $activity), [
                'attendances' => [[
                    'si_member_id' => $member->id,
                    'status' => SiAttendanceStatus::Present->value,
                    'remarks' => null,
                ]],
            ]);

        $this->assertDatabaseHas('si_attendances', [
            'si_activity_id' => $activity->id,
            'si_member_id' => $member->id,
            'status' => SiAttendanceStatus::Present->value,
        ]);
        $this->assertDatabaseCount('si_attendances', 1);
    }

    public function test_attendance_status_must_be_valid(): void
    {
        $activity = SiActivity::factory()->create();
        $member = SiMember::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('si.attendance.store', $activity), [
                'attendances' => [[
                    'si_member_id' => $member->id,
                    'status' => 'invalid_status',
                ]],
            ]);

        $response->assertSessionHasErrors('attendances.0.status');
    }

    public function test_guest_cannot_save_attendance(): void
    {
        $activity = SiActivity::factory()->create();

        $this->post(route('si.attendance.store', $activity), [
            'attendances' => [],
        ])->assertRedirect();
    }
}
