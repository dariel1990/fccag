<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Classification;
use App\Models\User;

final class ClassificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Read);
    }

    public function view(User $user, Classification $classification): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Create);
    }

    public function update(User $user, Classification $classification): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Update);
    }

    public function delete(User $user, Classification $classification): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Classifications, PermissionAction::Reports);
    }
}
