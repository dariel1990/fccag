<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Activity;
use App\Models\User;

final class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Read);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Create);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Update);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Activities, PermissionAction::Reports);
    }
}
