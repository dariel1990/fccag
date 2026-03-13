<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_activity_types(): void
    {
        ActivityType::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('activity-types.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('activity-types/Index')
                ->has('activityTypes', 3)
            );
    }

    public function test_create_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('activity-types.create'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('activity-types/Create')
            );
    }

    public function test_activity_type_can_be_created(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('activity-types.store'), [
                'name' => 'Sunday Service',
                'description' => 'Weekly Sunday worship',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('activity-types.index'));

        $this->assertDatabaseHas('activity_types', [
            'name' => 'Sunday Service',
        ]);
    }

    public function test_activity_type_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('activity-types.store'), [
                'name' => '',
                'description' => 'Test description',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_activity_type_name_must_be_unique(): void
    {
        ActivityType::factory()->create(['name' => 'Sunday Service']);

        $response = $this->actingAs($this->user)
            ->post(route('activity-types.store'), [
                'name' => 'Sunday Service',
                'description' => 'Another description',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_edit_page_is_displayed(): void
    {
        $activityType = ActivityType::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('activity-types.edit', $activityType));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('activity-types/Edit')
                ->where('activityType.id', $activityType->id)
            );
    }

    public function test_activity_type_can_be_updated(): void
    {
        $activityType = ActivityType::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put(route('activity-types.update', $activityType), [
                'name' => 'New Name',
                'description' => 'Updated description',
                'is_active' => false,
            ]);

        $response->assertRedirect(route('activity-types.index'));

        $this->assertDatabaseHas('activity_types', [
            'id' => $activityType->id,
            'name' => 'New Name',
            'is_active' => false,
        ]);
    }

    public function test_activity_type_can_be_deleted(): void
    {
        $activityType = ActivityType::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('activity-types.destroy', $activityType));

        $response->assertRedirect(route('activity-types.index'));

        $this->assertDatabaseMissing('activity_types', [
            'id' => $activityType->id,
        ]);
    }

    public function test_deleting_activity_type_cascades_to_activities(): void
    {
        $activityType = ActivityType::factory()->create();
        Activity::factory(3)->create(['activity_type_id' => $activityType->id]);

        $this->actingAs($this->user)
            ->delete(route('activity-types.destroy', $activityType));

        $this->assertDatabaseCount('activities', 0);
    }
}
