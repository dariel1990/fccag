<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Post;
use App\Models\User;

final class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Read);
    }

    public function view(User $user, Post $post): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Create);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Update);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Posts, PermissionAction::Reports);
    }
}
