<?php

namespace Tests\Feature;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_not_forbidden(): void
    {
        $response = $this->get(route('activities.index'));

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    public function test_superadmin_can_access_any_module_route(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('participants.index'))
            ->assertOk();
    }

    public function test_user_with_read_permission_on_module_can_access_it(): void
    {
        $user = User::factory()->withPermissions([
            Module::Activities->value => [PermissionAction::Read->value],
        ])->create();

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertOk();
    }

    public function test_user_without_any_permissions_gets_403_on_protected_route(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertForbidden();
    }

    public function test_user_with_full_access_can_access_all_modules(): void
    {
        $user = User::factory()->withFullAccess()->create();

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('participants.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('cell-groups.index'))
            ->assertOk();
    }

    public function test_user_with_permission_on_one_module_cannot_access_another(): void
    {
        $user = User::factory()->withPermissions([
            Module::Activities->value => [PermissionAction::Read->value],
        ])->create();

        $this->actingAs($user)
            ->get(route('participants.index'))
            ->assertForbidden();
    }
}
