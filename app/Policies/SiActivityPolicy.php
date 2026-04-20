<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\SiActivity;
use App\Models\User;

final class SiActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Read);
    }

    public function view(User $user, SiActivity $siActivity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Create);
    }

    public function update(User $user, SiActivity $siActivity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Update);
    }

    public function delete(User $user, SiActivity $siActivity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivities, PermissionAction::Reports);
    }
}
