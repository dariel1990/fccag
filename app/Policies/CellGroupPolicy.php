<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\CellGroup;
use App\Models\User;

final class CellGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Read);
    }

    public function view(User $user, CellGroup $cellGroup): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Create);
    }

    public function update(User $user, CellGroup $cellGroup): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Update);
    }

    public function delete(User $user, CellGroup $cellGroup): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::CellGroups, PermissionAction::Reports);
    }
}
