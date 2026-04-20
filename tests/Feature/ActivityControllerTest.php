<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ActivityType $activityType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withFullAccess()->create();
        $this->activityType = ActivityType::factory()->create();
    }

    public function test_index_displays_activities(): void
    {
        Activity::factory(3)->create([
            'activity_type_id' => $this->activityType->id,
            'activity_date' => now()->startOfMonth(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('activities.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('activities/Index')
                ->has('activities', 3)
                ->has('activityTypes')
            );
    }

    public function test_activity_can_be_created(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('activities.store'), [
                'activity_type_id' => $this->activityType->id,
                'title' => 'Sunday Service - Week 1',
                'description' => 'First Sunday of the month',
                'activity_date' => '2024-01-07',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'title' => 'Sunday Service - Week 1',
            'activity_type_id' => $this->activityType->id,
        ]);
    }

    public function test_activity_requires_title(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('activities.store'), [
                'activity_type_id' => $this->activityType->id,
                'title' => '',
                'activity_date' => '2024-01-07',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_activity_requires_valid_activity_type(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('activities.store'), [
                'activity_type_id' => 999,
                'title' => 'Test Activity',
                'activity_date' => '2024-01-07',
            ]);

        $response->assertSessionHasErrors('activity_type_id');
    }

    public function test_show_page_displays_activity_with_attendance(): void
    {
        $activity = Activity::factory()->create(['activity_type_id' => $this->activityType->id]);
        Participant::factory(3)->create(['is_active' => true]);

        $response = $this->actingAs($this->user)
            ->get(route('activities.show', $activity));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('activities/Show')
                ->where('activity.id', $activity->id)
                ->has('participants')
            );
    }

    public function test_activity_can_be_updated(): void
    {
        $activity = Activity::factory()->create(['activity_type_id' => $this->activityType->id]);

        $response = $this->actingAs($this->user)
            ->put(route('activities.update', $activity), [
                'activity_type_id' => $this->activityType->id,
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'activity_date' => '2024-02-14',
            ]);

        $response->assertRedirect(route('activities.index'));

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_activity_can_be_deleted(): void
    {
        $activity = Activity::factory()->create(['activity_type_id' => $this->activityType->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('activities.destroy', $activity));

        $response->assertRedirect(route('activities.index'));

        $this->assertDatabaseMissing('activities', [
            'id' => $activity->id,
        ]);
    }
}
