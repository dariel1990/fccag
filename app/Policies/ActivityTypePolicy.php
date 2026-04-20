<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\ActivityType;
use App\Models\User;

final class ActivityTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Read);
    }

    public function view(User $user, ActivityType $activityType): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Create);
    }

    public function update(User $user, ActivityType $activityType): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Update);
    }

    public function delete(User $user, ActivityType $activityType): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::ActivityTypes, PermissionAction::Reports);
    }
}
