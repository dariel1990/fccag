<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Pastor;
use App\Models\User;

final class PastorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Read);
    }

    public function view(User $user, Pastor $pastor): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Create);
    }

    public function update(User $user, Pastor $pastor): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Update);
    }

    public function delete(User $user, Pastor $pastor): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Pastors, PermissionAction::Reports);
    }
}
