<?php

namespace App\Enums;

enum SiActivityAssessment: string
{
    case Active = 'active';
    case Energetic = 'energetic';
    case Inactive = 'inactive';
    case Disobedient = 'disobedient';
    case Rebellious = 'rebellious';
    case Misbehaving = 'misbehaving';
    case Uncooperative = 'uncooperative';
    case UpToDate = 'up_to_date';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Energetic => 'Energetic',
            self::Inactive => 'Inactive',
            self::Disobedient => 'Disobedient',
            self::Rebellious => 'Rebellious',
            self::Misbehaving => 'Misbehaving',
            self::Uncooperative => 'Uncooperative',
            self::UpToDate => 'Up-to-date',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'blue',
            self::Energetic => 'green',
            self::Inactive => 'gray',
            self::Disobedient => 'orange',
            self::Rebellious => 'red',
            self::Misbehaving => 'rose',
            self::Uncooperative => 'amber',
            self::UpToDate => 'teal',
        };
    }

    public static function fromPercentage(float $percentage): self
    {
        return match (true) {
            $percentage >= 85 => self::UpToDate,
            $percentage >= 70 => self::Energetic,
            $percentage >= 50 => self::Active,
            $percentage >= 35 => self::Inactive,
            default => self::Uncooperative,
        };
    }
}
