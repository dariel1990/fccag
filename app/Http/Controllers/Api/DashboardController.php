<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Participant;
use App\Services\SpiritualLevelService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private SpiritualLevelService $spiritualLevelService) {}

    /**
     * Return dashboard stats for mobile.
     */
    public function index(): JsonResponse
    {
        $currentYear = now()->year;
        $currentQuarter = (int) ceil(now()->month / 3);
        $dates = $this->spiritualLevelService->getQuarterDates($currentYear, $currentQuarter);

        $stats = [
            'total_participants' => Participant::where('is_active', true)->count(),
            'total_activities_this_quarter' => Activity::whereBetween('activity_date', [
                $dates['start'],
                $dates['end'],
            ])->count(),
            'activities_recorded_this_month' => Activity::whereMonth('activity_date', now()->month)
                ->whereYear('activity_date', now()->year)
                ->count(),
        ];

        $recentActivities = Activity::with(['activityType', 'attendances'])
            ->latest('activity_date')
            ->limit(5)
            ->get()
            ->map(fn ($activity) => [
                'id' => $activity->id,
                'title' => $activity->title,
                'activity_type' => $activity->activityType->name,
                'activity_date' => $activity->activity_date->format('Y-m-d'),
                'attendance_count' => $activity->attendances->count(),
                'present_count' => $activity->attendances->where('is_present', true)->count(),
            ]);

        // Chart data for mobile
        $spiritualLevelDistribution = $this->getSpiritualLevelDistribution($currentYear, $currentQuarter);
        $attendanceTrend = $this->getAttendanceTrend();
        $activityTypeStats = $this->getActivityTypeStats($currentYear, $currentQuarter);

        return response()->json([
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'current_quarter' => "Q{$currentQuarter} {$currentYear}",
            'spiritual_level_distribution' => $spiritualLevelDistribution,
            'attendance_trend' => $attendanceTrend,
            'activity_type_stats' => $activityTypeStats,
        ]);
    }

    private function getSpiritualLevelDistribution(int $year, int $quarter): array
    {
        $summary = $this->spiritualLevelService->getQuarterlySummary($year, $quarter);

        return [
            'labels' => ['Spiritually Mature', 'Growing', 'Developing', 'Needs Guidance'],
            'data' => [
                $summary['spiritually_mature'],
                $summary['growing'],
                $summary['developing'],
                $summary['needs_guidance'],
            ],
            'colors' => ['#22c55e', '#3b82f6', '#eab308', '#ef4444'],
        ];
    }

    private function getAttendanceTrend(): array
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $totalActivities = Activity::whereMonth('activity_date', $date->month)
                ->whereYear('activity_date', $date->year)
                ->count();

            if ($totalActivities === 0) {
                $data[] = 0;

                continue;
            }

            $totalAttendances = Attendance::whereHas('activity', fn ($query) => $query
                ->whereMonth('activity_date', $date->month)
                ->whereYear('activity_date', $date->year)
            )->count();

            $presentAttendances = Attendance::where('is_present', true)
                ->whereHas('activity', fn ($query) => $query
                    ->whereMonth('activity_date', $date->month)
                    ->whereYear('activity_date', $date->year)
                )->count();

            $data[] = $totalAttendances > 0
                ? round(($presentAttendances / $totalAttendances) * 100, 1)
                : 0;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    private function getActivityTypeStats(int $year, int $quarter): array
    {
        $dates = $this->spiritualLevelService->getQuarterDates($year, $quarter);

        return Activity::withCount([
            'attendances',
            'attendances as present_count' => fn ($query) => $query->where('is_present', true),
        ])
            ->whereBetween('activity_date', [$dates['start'], $dates['end']])
            ->get()
            ->groupBy('activity_type_id')
            ->map(function ($activities) {
                $firstActivity = $activities->first();
                $totalAttendances = $activities->sum('attendances_count');
                $totalPresent = $activities->sum('present_count');

                return [
                    'name' => $firstActivity->activityType->name,
                    'activity_count' => $activities->count(),
                    'average_attendance' => $totalAttendances > 0
                        ? round(($totalPresent / $totalAttendances) * 100, 1)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }
}
