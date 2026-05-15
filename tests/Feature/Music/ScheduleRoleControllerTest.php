<?php

namespace Tests\Feature\Music;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\ScheduleRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->withPermissions([
            Module::ScheduleRoles->value => [
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
        ScheduleRole::factory(2)->create();

        $this->actingAs($user)
            ->get(route('music.schedule-roles.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('music/schedule-roles/Index')->has('roles', 2));
    }

    public function test_store_creates_role(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('music.schedule-roles.store'), [
                'team' => 'band',
                'name' => 'Percussion',
                'sort_order' => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('schedule_roles', ['team' => 'band', 'name' => 'Percussion']);
    }

    public function test_update_updates_role(): void
    {
        $user = $this->user();
        $role = ScheduleRole::factory()->create(['team' => 'band', 'name' => 'Old']);

        $this->actingAs($user)
            ->put(route('music.schedule-roles.update', $role), [
                'team' => 'band',
                'name' => 'New',
                'sort_order' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('schedule_roles', ['id' => $role->id, 'name' => 'New']);
    }

    public function test_destroy_deletes_role(): void
    {
        $user = $this->user();
        $role = ScheduleRole::factory()->create();

        $this->actingAs($user)
            ->delete(route('music.schedule-roles.destroy', $role))
            ->assertRedirect();

        $this->assertDatabaseMissing('schedule_roles', ['id' => $role->id]);
    }
}
