<?php

namespace App\Enums;

enum SiMemberSex: string
{
    case Male = 'male';
    case Female = 'female';
    case Unborn = 'unborn';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
            self::Unborn => 'Unborn',
        };
    }
}
