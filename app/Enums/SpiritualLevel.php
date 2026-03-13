<?php

namespace App\Enums;

enum SpiritualLevel: string
{
    case SpirituallyMature = 'Spiritually Mature';
    case Growing = 'Growing';
    case Developing = 'Developing';
    case NeedsGuidance = 'Needs Guidance';

    /**
     * Determine the spiritual level from an attendance percentage.
     */
    public static function fromPercentage(float $percentage): self
    {
        return match (true) {
            $percentage >= 90 => self::SpirituallyMature,
            $percentage >= 70 => self::Growing,
            $percentage >= 50 => self::Developing,
            default => self::NeedsGuidance,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SpirituallyMature => 'green',
            self::Growing => 'blue',
            self::Developing => 'yellow',
            self::NeedsGuidance => 'red',
        };
    }
}
