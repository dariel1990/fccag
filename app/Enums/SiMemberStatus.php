<?php

namespace App\Enums;

enum SiMemberStatus: string
{
    case Active = 'active';
    case Exit = 'exit';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Exit => 'Exit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Exit => 'red',
        };
    }
}
