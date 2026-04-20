<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Ministry;
use App\Models\User;

final class MinistryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Read);
    }

    public function view(User $user, Ministry $ministry): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Create);
    }

    public function update(User $user, Ministry $ministry): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Update);
    }

    public function delete(User $user, Ministry $ministry): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Ministries, PermissionAction::Reports);
    }
}
