<?php

namespace Tests\Feature;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;
use App\Services\UserPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserPermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserPermissionService;
    }

    public function test_grant_adds_action_to_module(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->service->grant($user, Module::Activities, [PermissionAction::Read]);

        $user->refresh();
        $this->assertEquals(['read'], $user->permissions['activities']);
    }

    public function test_grant_merges_with_existing_actions_without_duplicates(): void
    {
        $user = User::factory()->withPermissions(['activities' => ['read']])->create();

        $this->service->grant($user, Module::Activities, [PermissionAction::Read, PermissionAction::Create]);

        $user->refresh();
        $this->assertEqualsCanonicalizing(['read', 'create'], $user->permissions['activities']);
    }

    public function test_revoke_removes_specific_action(): void
    {
        $user = User::factory()->withPermissions(['activities' => ['read', 'create', 'delete']])->create();

        $this->service->revoke($user, Module::Activities, [PermissionAction::Create]);

        $user->refresh();
        $this->assertEqualsCanonicalizing(['read', 'delete'], $user->permissions['activities']);
    }

    public function test_revoke_removes_module_key_entirely_when_no_actions_remain(): void
    {
        $user = User::factory()->withPermissions(['activities' => ['read']])->create();

        $this->service->revoke($user, Module::Activities, [PermissionAction::Read]);

        $user->refresh();
        $this->assertArrayNotHasKey('activities', $user->permissions ?? []);
    }

    public function test_sync_replaces_all_permissions(): void
    {
        $user = User::factory()->withPermissions(['activities' => ['read', 'create']])->create();

        $this->service->sync($user, ['participants' => ['read']]);

        $user->refresh();
        $this->assertEquals(['participants' => ['read']], $user->permissions);
    }

    public function test_grant_full_access_sets_wildcard_true(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->service->grantFullAccess($user);

        $user->refresh();
        $this->assertEquals(['*' => true], $user->permissions);
    }

    public function test_revoke_all_sets_permissions_to_null(): void
    {
        $user = User::factory()->withFullAccess()->create();

        $this->service->revokeAll($user);

        $user->refresh();
        $this->assertNull($user->permissions);
    }
}
