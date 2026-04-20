<?php

namespace App\Policies;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Participant;
use App\Models\User;

final class ParticipantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Read);
    }

    public function view(User $user, Participant $participant): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Read);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Create);
    }

    public function update(User $user, Participant $participant): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Update);
    }

    public function delete(User $user, Participant $participant): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Delete);
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Export);
    }

    public function import(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Import);
    }

    public function reports(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission(Module::Participants, PermissionAction::Reports);
    }
}
