<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Setlist;
use App\Models\User;

final class SetlistPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Setlists, PermissionAction::Read);
    }

    public function view(User $user, Setlist $setlist): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Setlists, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Setlists, PermissionAction::Create);
    }

    public function update(User $user, Setlist $setlist): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Setlists, PermissionAction::Update);
    }

    public function delete(User $user, Setlist $setlist): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Setlists, PermissionAction::Delete);
    }
}
