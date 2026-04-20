<?php

namespace App\Enums;

enum SiAttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Exit = 'exit';
    case GaveBirth = 'gave_birth';
    case ChildSick = 'child_sick';
    case ChildUnderMedication = 'child_under_medication';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Exit => 'Exit',
            self::GaveBirth => 'Gave Birth',
            self::ChildSick => 'Child Sick',
            self::ChildUnderMedication => 'Child under Medication',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Present => 'green',
            self::Absent => 'red',
            self::Exit => 'gray',
            self::GaveBirth => 'blue',
            self::ChildSick => 'yellow',
            self::ChildUnderMedication => 'orange',
        };
    }

    public function countsAsPresent(): bool
    {
        return $this === self::Present;
    }
}
