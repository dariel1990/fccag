<?php

namespace App\Services;

use App\Enums\SpiritualLevel;
use App\Models\Attendance;
use App\Models\Participant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class SpiritualLevelService
{
    /**
     * Get the start and end dates for a given quarter.
     *
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function getQuarterDates(int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;

        return [
            'start' => CarbonImmutable::create($year, $startMonth, 1)->startOfDay(),
            'end' => CarbonImmutable::create($year, $startMonth + 2, 1)->endOfMonth()->endOfDay(),
        ];
    }

    /**
     * Calculate the attendance percentage for a participant in a given quarter.
     */
    public function calculateAttendancePercentage(Participant $participant, int $year, int $quarter): float
    {
        $dates = $this->getQuarterDates($year, $quarter);

        $totalActivities = Attendance::query()
            ->where('person_id', $participant->id)
            ->whereHas('activity', fn ($query) => $query->whereBetween('activity_date', [$dates['start'], $dates['end']]))
            ->count();

        if ($totalActivities === 0) {
            return 0.0;
        }

        $presentCount = Attendance::query()
            ->where('person_id', $participant->id)
            ->where('is_present', true)
            ->whereHas('activity', fn ($query) => $query->whereBetween('activity_date', [$dates['start'], $dates['end']]))
            ->count();

        return round(($presentCount / $totalActivities) * 100, 2);
    }

    /**
     * Get the spiritual level for a participant in a given quarter.
     */
    public function getSpiritualLevel(Participant $participant, int $year, int $quarter): SpiritualLevel
    {
        $percentage = $this->calculateAttendancePercentage($participant, $year, $quarter);

        return SpiritualLevel::fromPercentage($percentage);
    }

    /**
     * Generate the quarterly report for all active participants.
     *
     * @return Collection<int, array{
     *     participant_id: int,
     *     full_name: string,
     *     cell_group: string|null,
     *     total_activities: int,
     *     attended: int,
     *     percentage: float,
     *     spiritual_level: string,
     *     spiritual_level_color: string,
     * }>
     */
    public function generateQuarterlyReport(int $year, int $quarter): Collection
    {
        $dates = $this->getQuarterDates($year, $quarter);

        $participants = Participant::query()
            ->with('cellGroup')
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return $participants->map(function (Participant $participant) use ($dates) {
            $totalActivities = Attendance::query()
                ->where('person_id', $participant->id)
                ->whereHas('activity', fn ($query) => $query->whereBetween('activity_date', [$dates['start'], $dates['end']]))
                ->count();

            $attended = Attendance::query()
                ->where('person_id', $participant->id)
                ->where('is_present', true)
                ->whereHas('activity', fn ($query) => $query->whereBetween('activity_date', [$dates['start'], $dates['end']]))
                ->count();

            $percentage = $totalActivities > 0
                ? round(($attended / $totalActivities) * 100, 2)
                : 0.0;

            $level = SpiritualLevel::fromPercentage($percentage);

            return [
                'participant_id' => $participant->id,
                'full_name' => $participant->full_name,
                'cell_group' => $participant->cellGroup?->name,
                'total_activities' => $totalActivities,
                'attended' => $attended,
                'percentage' => $percentage,
                'spiritual_level' => $level->value,
                'spiritual_level_color' => $level->color(),
            ];
        });
    }

    /**
     * Get summary statistics for the quarterly report.
     *
     * @return array{
     *     total_participants: int,
     *     spiritually_mature: int,
     *     growing: int,
     *     developing: int,
     *     needs_guidance: int,
     *     average_attendance: float,
     * }
     */
    public function getQuarterlySummary(int $year, int $quarter): array
    {
        $report = $this->generateQuarterlyReport($year, $quarter);

        $totalParticipants = $report->count();
        $averageAttendance = $totalParticipants > 0
            ? round($report->avg('percentage'), 2)
            : 0.0;

        return [
            'total_participants' => $totalParticipants,
            'spiritually_mature' => $report->where('spiritual_level', SpiritualLevel::SpirituallyMature->value)->count(),
            'growing' => $report->where('spiritual_level', SpiritualLevel::Growing->value)->count(),
            'developing' => $report->where('spiritual_level', SpiritualLevel::Developing->value)->count(),
            'needs_guidance' => $report->where('spiritual_level', SpiritualLevel::NeedsGuidance->value)->count(),
            'average_attendance' => $averageAttendance,
        ];
    }
}
