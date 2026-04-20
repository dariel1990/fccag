<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Department;
use App\Models\User;

final class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Read);
    }

    public function view(User $user, Department $department): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Create);
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Update);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Departments, PermissionAction::Reports);
    }
}
