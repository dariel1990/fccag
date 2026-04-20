<?php

namespace App\Enums;

enum SiSpiritualAssessment: string
{
    case Developing = 'developing';
    case Growing = 'growing';
    case NeedsGuidance = 'needs_guidance';
    case SpirituallyMature = 'spiritually_mature';
    case Responsive = 'responsive';
    case Neglectful = 'neglectful';

    public function label(): string
    {
        return match ($this) {
            self::Developing => 'Developing (Still Learning)',
            self::Growing => 'Growing (Shows good understanding)',
            self::NeedsGuidance => 'Needs Guidance (Needs Mentoring)',
            self::SpirituallyMature => 'Spiritually Mature (Applies teaching)',
            self::Responsive => 'Responsive',
            self::Neglectful => 'Neglectful',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Developing => 'blue',
            self::Growing => 'green',
            self::NeedsGuidance => 'orange',
            self::SpirituallyMature => 'purple',
            self::Responsive => 'teal',
            self::Neglectful => 'red',
        };
    }

    public static function fromPercentage(float $percentage): self
    {
        return match (true) {
            $percentage >= 90 => self::SpirituallyMature,
            $percentage >= 75 => self::Growing,
            $percentage >= 55 => self::Developing,
            $percentage >= 40 => self::NeedsGuidance,
            default => self::Neglectful,
        };
    }
}
