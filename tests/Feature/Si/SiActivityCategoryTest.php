<?php

namespace Tests\Feature\Si;

use App\Models\SiActivityCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiActivityCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withFullAccess()->create();
    }

    public function test_index_displays_categories(): void
    {
        SiActivityCategory::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('si.activity-categories.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/activity-categories/Index')
                ->has('categories', 3)
            );
    }

    public function test_index_includes_categories(): void
    {
        SiActivityCategory::factory(2)->create();

        $response = $this->actingAs($this->user)
            ->get(route('si.activity-categories.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/activity-categories/Index')
                ->has('categories', 2)
            );
    }

    public function test_category_can_be_created(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('si.activity-categories.store'), [
                'name' => 'Life Class',
                'weight' => 0.30,
                'is_active' => true,
            ]);

        $response->assertRedirect(route('si.activity-categories.index'));

        $this->assertDatabaseHas('si_activity_categories', [
            'name' => 'Life Class',
        ]);
    }

    public function test_category_requires_name_and_weight(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('si.activity-categories.store'), []);

        $response->assertSessionHasErrors(['name', 'weight']);
    }

    public function test_weight_must_be_between_0_and_1(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('si.activity-categories.store'), [
                'name' => 'Life Class',
                'weight' => 1.5,
            ]);

        $response->assertSessionHasErrors('weight');
    }

    public function test_category_can_be_updated_and_redirects(): void
    {
        $category = SiActivityCategory::factory()->create(['name' => 'Before']);

        $response = $this->actingAs($this->user)
            ->put(route('si.activity-categories.update', $category), [
                'name' => 'After',
                'weight' => 0.25,
                'is_active' => true,
            ]);

        $response->assertRedirect(route('si.activity-categories.index'));
        $this->assertDatabaseHas('si_activity_categories', ['id' => $category->id, 'name' => 'After']);
    }

    public function test_category_can_be_updated(): void
    {
        $category = SiActivityCategory::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put(route('si.activity-categories.update', $category), [
                'name' => 'New Name',
                'weight' => 0.25,
                'is_active' => false,
            ]);

        $response->assertRedirect(route('si.activity-categories.index'));

        $this->assertDatabaseHas('si_activity_categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'is_active' => false,
        ]);
    }

    public function test_category_can_be_deleted(): void
    {
        $category = SiActivityCategory::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('si.activity-categories.destroy', $category));

        $response->assertRedirect(route('si.activity-categories.index'));

        $this->assertDatabaseMissing('si_activity_categories', ['id' => $category->id]);
    }

    public function test_guest_cannot_access_categories(): void
    {
        $this->get(route('si.activity-categories.index'))->assertRedirect();
    }
}
