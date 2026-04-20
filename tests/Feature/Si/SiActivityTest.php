<?php

namespace Tests\Feature\Si;

use App\Models\SiActivity;
use App\Models\SiActivityCategory;
use App\Models\SiMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withFullAccess()->create();
    }

    public function test_index_displays_activities(): void
    {
        SiActivity::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('si.activities.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/activities/Index')
                ->has('activities', 3)
            );
    }

    public function test_index_includes_attendance_data(): void
    {
        $activity = SiActivity::factory()->create();
        $member = SiMember::factory()->create();
        $activity->assignedMembers()->attach($member->id);

        $response = $this->actingAs($this->user)
            ->get(route('si.activities.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/activities/Index')
                ->has('attendanceStatuses')
                ->has('activities.0.members')
            );
    }

    public function test_activity_can_be_created_with_assigned_members(): void
    {
        $category = SiActivityCategory::factory()->create();
        $members = SiMember::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->post(route('si.activities.store'), [
                'si_activity_category_id' => $category->id,
                'title' => 'Life Class Zone 1',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Responsive Caregiving',
                'memory_verse' => 'Isaiah 65:24',
                'conducted_at' => '2025-07-18',
                'member_ids' => $members->pluck('id')->toArray(),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('si_activities', ['title' => 'Life Class Zone 1']);

        $activity = SiActivity::where('title', 'Life Class Zone 1')->first();
        $this->assertCount(3, $activity->assignedMembers);
    }

    public function test_activity_requires_category_title_date_and_members(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('si.activities.store'), []);

        $response->assertSessionHasErrors(['si_activity_category_id', 'title', 'conducted_at', 'member_ids']);
    }

    public function test_show_page_is_displayed(): void
    {
        $activity = SiActivity::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('si.activities.show', $activity));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/activities/Show')
                ->where('siActivity.id', $activity->id)
            );
    }

    public function test_activity_can_be_updated_and_redirects_to_show(): void
    {
        $activity = SiActivity::factory()->create(['title' => 'Before Update']);
        $category = SiActivityCategory::factory()->create();
        $members = SiMember::factory(2)->create();

        $response = $this->actingAs($this->user)
            ->put(route('si.activities.update', $activity), [
                'si_activity_category_id' => $category->id,
                'title' => 'After Update',
                'conducted_at' => '2025-09-01',
                'member_ids' => $members->pluck('id')->toArray(),
            ]);

        $response->assertRedirect(route('si.activities.index'));
        $this->assertDatabaseHas('si_activities', ['id' => $activity->id, 'title' => 'After Update']);
    }

    public function test_activity_can_be_updated(): void
    {
        $activity = SiActivity::factory()->create(['title' => 'Old Title']);
        $category = SiActivityCategory::factory()->create();
        $members = SiMember::factory(2)->create();

        $response = $this->actingAs($this->user)
            ->put(route('si.activities.update', $activity), [
                'si_activity_category_id' => $category->id,
                'title' => 'New Title',
                'conducted_at' => '2025-08-01',
                'member_ids' => $members->pluck('id')->toArray(),
            ]);

        $response->assertRedirect(route('si.activities.index'));

        $this->assertDatabaseHas('si_activities', [
            'id' => $activity->id,
            'title' => 'New Title',
        ]);
    }

    public function test_activity_can_be_deleted(): void
    {
        $activity = SiActivity::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('si.activities.destroy', $activity));

        $response->assertRedirect(route('si.activities.index'));

        $this->assertDatabaseMissing('si_activities', ['id' => $activity->id]);
    }

    public function test_guest_cannot_access_activities(): void
    {
        $this->get(route('si.activities.index'))->assertRedirect();
    }
}
