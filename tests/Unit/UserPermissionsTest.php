<?php

namespace Tests\Unit;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\ValueObjects\UserPermissions;
use PHPUnit\Framework\TestCase;

class UserPermissionsTest extends TestCase
{
    public function test_can_returns_true_for_wildcard(): void
    {
        $permissions = UserPermissions::fromArray(['*' => true]);

        $this->assertTrue($permissions->can(Module::Activities, PermissionAction::Read));
        $this->assertTrue($permissions->can(Module::Participants, PermissionAction::Delete));
    }

    public function test_can_returns_true_for_granted_action(): void
    {
        $permissions = UserPermissions::fromArray([
            'activities' => ['read', 'create'],
        ]);

        $this->assertTrue($permissions->can(Module::Activities, PermissionAction::Read));
        $this->assertTrue($permissions->can(Module::Activities, PermissionAction::Create));
    }

    public function test_can_returns_false_for_missing_action(): void
    {
        $permissions = UserPermissions::fromArray([
            'activities' => ['read'],
        ]);

        $this->assertFalse($permissions->can(Module::Activities, PermissionAction::Delete));
        $this->assertFalse($permissions->can(Module::Activities, PermissionAction::Update));
    }

    public function test_can_returns_false_when_module_not_present(): void
    {
        $permissions = UserPermissions::fromArray([
            'activities' => ['read'],
        ]);

        $this->assertFalse($permissions->can(Module::Participants, PermissionAction::Read));
    }

    public function test_from_array_null_returns_empty_all_false(): void
    {
        $permissions = UserPermissions::fromArray(null);

        $this->assertFalse($permissions->can(Module::Activities, PermissionAction::Read));
        $this->assertFalse($permissions->can(Module::Participants, PermissionAction::Create));
    }

    public function test_to_array_returns_original_array(): void
    {
        $data = ['activities' => ['read', 'create'], 'participants' => ['read']];
        $permissions = UserPermissions::fromArray($data);

        $this->assertEquals($data, $permissions->toArray());
    }
}
