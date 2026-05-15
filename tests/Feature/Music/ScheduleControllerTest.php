<?php

namespace Tests\Feature\Music;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\MusicMember;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRole;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    private function userWithSchedules(): User
    {
        return User::factory()->withPermissions([
            Module::Schedules->value => [
                PermissionAction::Read->value,
                PermissionAction::Create->value,
                PermissionAction::Update->value,
                PermissionAction::Delete->value,
            ],
        ])->create();
    }

    private function seedRoles(): void
    {
        ScheduleRole::factory()->create(['team' => 'band', 'name' => 'Keys', 'sort_order' => 1]);
        ScheduleRole::factory()->create(['team' => 'band', 'name' => 'Drums', 'sort_order' => 2]);
        ScheduleRole::factory()->create(['team' => 'media', 'name' => 'Media', 'sort_order' => 1]);
        ScheduleRole::factory()->create(['team' => 'worship', 'name' => 'WL', 'sort_order' => 1]);
    }

    public function test_index_renders_unified_schedule_page(): void
    {
        $this->seedRoles();
        $user = $this->userWithSchedules();

        $response = $this->actingAs($user)->get(route('music.schedules.index'));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('music/schedules/Index')
                ->has('schedules')
                ->has('roles', 4)
                ->has('members')
                ->has('serviceTypes')
                ->where('year', now()->year)
                ->where('month', now()->month)
            );

        $teams = collect($response->viewData('page')['props']['roles'])->pluck('team')->unique()->values()->all();

        $this->assertContains('band', $teams);
        $this->assertContains('media', $teams);
        $this->assertContains('worship', $teams);
    }

    public function test_generate_month_creates_schedules_for_each_matching_weekday(): void
    {
        $user = $this->userWithSchedules();

        // Carbon weekday convention: 0=Sun, 4=Thu, 5=Fri
        ServiceType::factory()->create(['name' => 'Divine Service', 'day_of_week' => 0]);
        ServiceType::factory()->create(['name' => 'Cottage Service', 'day_of_week' => 4]);
        ServiceType::factory()->create(['name' => 'Prayer Meeting', 'day_of_week' => 5]);
        ServiceType::factory()->create(['name' => 'Prayer & Fasting', 'day_of_week' => null]);

        $this->actingAs($user)->post(route('music.schedules.generate'), [
            'year' => 2026,
            'month' => 5,
        ])->assertRedirect();

        // May 2026: 5 Sundays + 4 Thursdays + 5 Fridays = 14
        $this->assertSame(14, Schedule::query()->count());
    }

    public function test_generate_month_is_idempotent(): void
    {
        $user = $this->userWithSchedules();

        ServiceType::factory()->create(['name' => 'Divine Service', 'day_of_week' => 0]);
        ServiceType::factory()->create(['name' => 'Cottage Service', 'day_of_week' => 4]);
        ServiceType::factory()->create(['name' => 'Prayer Meeting', 'day_of_week' => 5]);

        $payload = ['year' => 2026, 'month' => 5];

        $this->actingAs($user)->post(route('music.schedules.generate'), $payload);
        $countAfterFirst = Schedule::query()->count();

        $this->actingAs($user)->post(route('music.schedules.generate'), $payload);
        $countAfterSecond = Schedule::query()->count();

        $this->assertSame(14, $countAfterFirst);
        $this->assertSame($countAfterFirst, $countAfterSecond);
    }

    public function test_update_assignments_upserts_across_all_teams(): void
    {
        $user = $this->userWithSchedules();

        $bandKeys = ScheduleRole::factory()->create(['team' => 'band', 'name' => 'Keys']);
        $mediaRole = ScheduleRole::factory()->create(['team' => 'media', 'name' => 'Media']);
        $worshipWL = ScheduleRole::factory()->create(['team' => 'worship', 'name' => 'WL']);

        $serviceType = ServiceType::factory()->create(['name' => 'Divine Service', 'day_of_week' => 0]);

        $schedule = Schedule::factory()->create([
            'service_type_id' => $serviceType->id,
            'service_date' => '2026-05-03', // a Sunday
            'created_by' => $user->id,
        ]);

        $originalKeysMember = MusicMember::factory()->create(['name' => 'Algene']);
        $newKeysMember = MusicMember::factory()->create(['name' => 'Daniel']);
        $mediaMember = MusicMember::factory()->create(['name' => 'Reaiah']);
        $worshipMember = MusicMember::factory()->create(['name' => 'Joy']);

        $existingBand = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $bandKeys->id,
            'music_member_id' => $originalKeysMember->id,
        ]);
        ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $mediaRole->id,
            'music_member_id' => $mediaMember->id,
        ]);

        // First PATCH: update band Keys, preserve media, add worship
        $this->actingAs($user)->patch(route('music.schedules.assignments.update', $schedule), [
            'assignments' => [
                ['schedule_role_id' => $bandKeys->id, 'music_member_id' => $newKeysMember->id],
                ['schedule_role_id' => $mediaRole->id, 'music_member_id' => $mediaMember->id],
                ['schedule_role_id' => $worshipWL->id, 'music_member_id' => $worshipMember->id],
            ],
        ])->assertRedirect();

        // Band Keys updated (not duplicated): same row id, new member
        $this->assertSame(1, ScheduleAssignment::query()
            ->where('schedule_id', $schedule->id)
            ->where('schedule_role_id', $bandKeys->id)
            ->count());
        $this->assertDatabaseHas('schedule_assignments', [
            'id' => $existingBand->id,
            'schedule_role_id' => $bandKeys->id,
            'music_member_id' => $newKeysMember->id,
        ]);

        // Media preserved
        $this->assertDatabaseHas('schedule_assignments', [
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $mediaRole->id,
            'music_member_id' => $mediaMember->id,
        ]);

        // Worship created
        $this->assertDatabaseHas('schedule_assignments', [
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $worshipWL->id,
            'music_member_id' => $worshipMember->id,
        ]);

        // Second PATCH: only band Keys → media + worship deleted (no team scoping)
        $this->actingAs($user)->patch(route('music.schedules.assignments.update', $schedule), [
            'assignments' => [
                ['schedule_role_id' => $bandKeys->id, 'music_member_id' => $newKeysMember->id],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('schedule_assignments', [
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $bandKeys->id,
        ]);
        $this->assertDatabaseMissing('schedule_assignments', [
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $mediaRole->id,
        ]);
        $this->assertDatabaseMissing('schedule_assignments', [
            'schedule_id' => $schedule->id,
            'schedule_role_id' => $worshipWL->id,
        ]);
    }

    public function test_index_requires_schedules_permission(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->actingAs($user)
            ->get(route('music.schedules.index'))
            ->assertForbidden();
    }
}
