<?php

namespace App\Http\Controllers;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Participant;
use App\Models\SiActivity;
use App\Models\SiAttendance;
use App\Models\SiMember;
use App\Services\SpiritualLevelService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private SpiritualLevelService $spiritualLevelService) {}

    /**
     * Display the dashboard with analytics based on user permissions.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $currentYear = now()->year;
        $currentQuarter = (int) ceil(now()->month / 3);

        $canViewParticipants = $user->hasPermission(Module::Participants, PermissionAction::Read);
        $canViewActivities = $user->hasPermission(Module::Activities, PermissionAction::Read);
        $canViewActivityTypes = $user->hasPermission(Module::ActivityTypes, PermissionAction::Read);
        $canViewSiMembers = $user->hasPermission(Module::SiMembers, PermissionAction::Read);
        $canViewSiActivities = $user->hasPermission(Module::SiActivities, PermissionAction::Read);

        $quarterDates = $this->spiritualLevelService->getQuarterDates($currentYear, $currentQuarter);

        return Inertia::render('Dashboard', [
            'currentQuarter' => "Q{$currentQuarter} {$currentYear}",

            // Core stats
            'stats' => [
                'total_participants' => $canViewParticipants
                    ? Participant::where('is_active', true)->count()
                    : null,
                'total_activities_this_quarter' => $canViewActivities
                    ? Activity::whereBetween('activity_date', [$quarterDates['start'], $quarterDates['end']])->count()
                    : null,
                'activities_recorded_this_month' => $canViewActivities
                    ? Activity::whereMonth('activity_date', now()->month)->whereYear('activity_date', now()->year)->count()
                    : null,
            ],

            // Core charts
            'spiritualLevelDistribution' => $canViewParticipants
                ? $this->getSpiritualLevelDistribution($currentYear, $currentQuarter)
                : null,
            'attendanceTrend' => $canViewActivities
                ? $this->getAttendanceTrend()
                : null,
            'recentActivities' => $canViewActivities
                ? Activity::with(['activityType', 'attendances'])
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
                    ])
                : null,
            'activityTypeStats' => $canViewActivityTypes
                ? $this->getActivityTypeStats($currentYear, $currentQuarter)
                : null,

            // SI stats
            'siStats' => ($canViewSiMembers || $canViewSiActivities)
                ? [
                    'total_active_members' => $canViewSiMembers
                        ? SiMember::where('status', 'active')->count()
                        : null,
                    'total_activities_this_month' => $canViewSiActivities
                        ? SiActivity::whereMonth('conducted_at', now()->month)->whereYear('conducted_at', now()->year)->count()
                        : null,
                    'attendance_rate_this_month' => $canViewSiActivities
                        ? $this->getSiAttendanceRateThisMonth()
                        : null,
                ]
                : null,
            'siRecentActivities' => $canViewSiActivities
                ? SiActivity::with(['category', 'siAttendances'])
                    ->latest('conducted_at')
                    ->limit(5)
                    ->get()
                    ->map(fn ($activity) => [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'category' => $activity->category->name,
                        'conducted_at' => $activity->conducted_at->format('Y-m-d'),
                        'present_count' => $activity->siAttendances->where('status', 'present')->count(),
                        'total_count' => $activity->siAttendances->count(),
                    ])
                : null,
            'siMemberStatusBreakdown' => $canViewSiMembers
                ? $this->getSiMemberStatusBreakdown()
                : null,
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
     * Get SI attendance rate for the current month.
     */
    private function getSiAttendanceRateThisMonth(): float
    {
        $total = SiAttendance::whereHas('siActivity', fn ($q) => $q
            ->whereMonth('conducted_at', now()->month)
            ->whereYear('conducted_at', now()->year)
        )->count();

        if ($total === 0) {
            return 0;
        }

        $present = SiAttendance::where('status', 'present')
            ->whereHas('siActivity', fn ($q) => $q
                ->whereMonth('conducted_at', now()->month)
                ->whereYear('conducted_at', now()->year)
            )->count();

        return round(($present / $total) * 100, 1);
    }

    /**
     * Get SI member status breakdown for chart.
     */
    private function getSiMemberStatusBreakdown(): array
    {
        $counts = SiMember::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'labels' => array_map('ucfirst', array_keys($counts)),
            'data' => array_values($counts),
            'colors' => ['#22c55e', '#ef4444', '#eab308', '#3b82f6', '#a855f7'],
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
