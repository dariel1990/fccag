<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Participant;
use App\Services\SpiritualLevelService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private SpiritualLevelService $spiritualLevelService) {}

    /**
     * Display the dashboard with analytics.
     */
    public function index(): Response
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);

        $stats = [
            'total_participants' => Participant::where('is_active', true)->count(),
            'total_activities_this_quarter' => Activity::whereBetween('activity_date', [
                $this->spiritualLevelService->getQuarterDates($currentYear, $currentQuarter)['start'],
                $this->spiritualLevelService->getQuarterDates($currentYear, $currentQuarter)['end'],
            ])->count(),
            'activities_recorded_this_month' => Activity::whereMonth('activity_date', now()->month)
                ->whereYear('activity_date', now()->year)
                ->count(),
        ];

        $spiritualLevelDistribution = $this->getSpiritualLevelDistribution($currentYear, $currentQuarter);

        $attendanceTrend = $this->getAttendanceTrend();

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

        $activityTypeStats = $this->getActivityTypeStats($currentYear, $currentQuarter);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'spiritualLevelDistribution' => $spiritualLevelDistribution,
            'attendanceTrend' => $attendanceTrend,
            'recentActivities' => $recentActivities,
            'activityTypeStats' => $activityTypeStats,
            'currentQuarter' => "Q{$currentQuarter} {$currentYear}",
        ]);
    }

    /**
     * Get spiritual level distribution for pie chart.
     */
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

    /**
     * Get attendance trend for the last 6 months.
     */
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

    /**
     * Get activity type statistics.
     */
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
