<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Song;
use App\Models\User;

final class SongPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Songs, PermissionAction::Read);
    }

    public function view(User $user, Song $song): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Songs, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Songs, PermissionAction::Create);
    }

    public function update(User $user, Song $song): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Songs, PermissionAction::Update);
    }

    public function delete(User $user, Song $song): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Songs, PermissionAction::Delete);
    }
}
