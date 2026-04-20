<?php

namespace App\ValueObjects;

use App\Enums\Module;
use App\Enums\PermissionAction;

final class UserPermissions
{
    public function __construct(private readonly array $permissions) {}

    public static function fromArray(?array $data): self
    {
        return new self($data ?? []);
    }

    public function can(Module $module, PermissionAction $action): bool
    {
        if (($this->permissions['*'] ?? false) === true) {
            return true;
        }

        $modulePerms = $this->permissions[$module->value] ?? [];

        return in_array($action->value, $modulePerms, strict: true);
    }

    public function toArray(): array
    {
        return $this->permissions;
    }
}
