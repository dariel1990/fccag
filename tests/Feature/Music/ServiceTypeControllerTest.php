<?php

namespace Tests\Feature\Music;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->withPermissions([
            Module::ServiceTypes->value => [
                PermissionAction::Read->value,
                PermissionAction::Create->value,
                PermissionAction::Update->value,
                PermissionAction::Delete->value,
            ],
        ])->create();
    }

    public function test_index_renders(): void
    {
        $user = $this->user();
        ServiceType::factory(2)->create();

        $this->actingAs($user)
            ->get(route('music.service-types.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('music/service-types/Index')->has('serviceTypes', 2));
    }

    public function test_store_creates_service_type(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('music.service-types.store'), [
                'name' => 'Divine Service',
                'day_of_week' => 0,
                'color' => '#fb923c',
                'sort_order' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_types', ['name' => 'Divine Service', 'day_of_week' => 0]);
    }

    public function test_update_updates_service_type(): void
    {
        $user = $this->user();
        $type = ServiceType::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->put(route('music.service-types.update', $type), [
                'name' => 'New Name',
                'day_of_week' => 4,
                'sort_order' => 0,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_types', ['id' => $type->id, 'name' => 'New Name', 'day_of_week' => 4]);
    }

    public function test_destroy_deletes_service_type(): void
    {
        $user = $this->user();
        $type = ServiceType::factory()->create();

        $this->actingAs($user)
            ->delete(route('music.service-types.destroy', $type))
            ->assertRedirect();

        $this->assertDatabaseMissing('service_types', ['id' => $type->id]);
    }
}
