<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\SiMember;
use App\Models\User;

final class SiMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Read);
    }

    public function view(User $user, SiMember $siMember): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Create);
    }

    public function update(User $user, SiMember $siMember): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Update);
    }

    public function delete(User $user, SiMember $siMember): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::SiMembers, PermissionAction::Reports);
    }
}
