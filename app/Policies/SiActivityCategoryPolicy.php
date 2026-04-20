<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\SiActivityCategory;
use App\Models\User;

final class SiActivityCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Read);
    }

    public function view(User $user, SiActivityCategory $siActivityCategory): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Create);
    }

    public function update(User $user, SiActivityCategory $siActivityCategory): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Update);
    }

    public function delete(User $user, SiActivityCategory $siActivityCategory): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiActivityCategories, PermissionAction::Reports);
    }
}
