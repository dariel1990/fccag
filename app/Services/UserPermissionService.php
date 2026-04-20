<?php

namespace App\Services;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\User;

final class UserPermissionService
{
    /**
     * Grant specific actions on a module to a user.
     *
     * @param  array<PermissionAction>  $actions
     */
    public function grant(User $user, Module $module, array $actions): void
    {
        $permissions = $user->permissions ?? [];
        $existing = $permissions[$module->value] ?? [];

        $newActions = array_values(array_unique([
            ...$existing,
            ...array_map(fn (PermissionAction $a) => $a->value, $actions),
        ]));

        $permissions[$module->value] = $newActions;

        $user->update(['permissions' => $permissions]);
    }

    /**
     * Revoke specific actions on a module from a user.
     *
     * @param  array<PermissionAction>  $actions
     */
    public function revoke(User $user, Module $module, array $actions): void
    {
        $permissions = $user->permissions ?? [];
        $actionValues = array_map(fn (PermissionAction $a) => $a->value, $actions);
        $existing = $permissions[$module->value] ?? [];

        $permissions[$module->value] = array_values(
            array_filter($existing, fn (string $a) => ! in_array($a, $actionValues, strict: true))
        );

        if (empty($permissions[$module->value])) {
            unset($permissions[$module->value]);
        }

        $user->update(['permissions' => $permissions]);
    }

    /**
     * Replace all permissions for a user at once.
     *
     * @param  array<string, array<string>|bool>  $permissions
     */
    public function sync(User $user, array $permissions): void
    {
        $user->update(['permissions' => $permissions]);
    }

    /**
     * Grant full access to all modules and actions.
     */
    public function grantFullAccess(User $user): void
    {
        $user->update(['permissions' => ['*' => true]]);
    }

    /**
     * Revoke all permissions from a user.
     */
    public function revokeAll(User $user): void
    {
        $user->update(['permissions' => null]);
    }
}
