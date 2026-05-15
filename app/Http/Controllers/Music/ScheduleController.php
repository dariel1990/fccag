<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\GenerateMonthScheduleRequest;
use App\Http\Requests\Music\StoreScheduleRequest;
use App\Http\Requests\Music\UpdateScheduleAssignmentsRequest;
use App\Http\Requests\Music\UpdateScheduleRequest;
use App\Models\MusicMember;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRole;
use App\Models\ServiceType;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(): Response
    {
        $year = (int) request('year', now()->year);
        $month = (int) request('month', now()->month);

        $start = CarbonImmutable::create($year, $month, 1)->startOfMonth();
        $end = $start->endOfMonth();

        $schedules = Schedule::query()
            ->with([
                'serviceType',
                'assignments',
                'assignments.role',
                'assignments.member',
            ])
            ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('service_date')
            ->get();

        $roles = ScheduleRole::query()
            ->orderBy('team')
            ->orderBy('sort_order')
            ->get(['id', 'team', 'name', 'sort_order']);

        $serviceTypes = ServiceType::query()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'day_of_week', 'color', 'sort_order']);

        $members = MusicMember::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'user_id']);

        return Inertia::render('music/schedules/Index', [
            'year' => $year,
            'month' => $month,
            'schedules' => $schedules,
            'serviceTypes' => $serviceTypes,
            'roles' => $roles,
            'members' => $members,
        ]);
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        Schedule::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->back();
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->back();
    }

    public function generateMonth(GenerateMonthScheduleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $start = CarbonImmutable::create($year, $month, 1)->startOfMonth();
        $end = $start->endOfMonth();

        $serviceTypes = ServiceType::query()
            ->whereNotNull('day_of_week')
            ->get();

        $userId = $request->user()->id;

        DB::transaction(function () use ($serviceTypes, $start, $end, $userId): void {
            foreach ($serviceTypes as $serviceType) {
                $cursor = $start;
                while ($cursor->lessThanOrEqualTo($end)) {
                    if ($cursor->dayOfWeek === (int) $serviceType->day_of_week) {
                        Schedule::firstOrCreate(
                            [
                                'service_type_id' => $serviceType->id,
                                'service_date' => $cursor->toDateString(),
                            ],
                            [
                                'status' => 'active',
                                'created_by' => $userId,
                            ]
                        );
                    }
                    $cursor = $cursor->addDay();
                }
            }
        });

        return redirect()
            ->route('music.schedules.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Schedules generated for the selected month.');
    }

    public function updateAssignments(UpdateScheduleAssignmentsRequest $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validated();
        $assignments = $validated['assignments'];

        DB::transaction(function () use ($schedule, $assignments): void {
            $submittedRoleIds = [];

            foreach ($assignments as $assignment) {
                $roleId = (int) $assignment['schedule_role_id'];

                ScheduleAssignment::updateOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'schedule_role_id' => $roleId,
                    ],
                    [
                        'music_member_id' => $assignment['music_member_id'] ?? null,
                        'notes' => $assignment['notes'] ?? null,
                    ]
                );

                $submittedRoleIds[] = $roleId;
            }

            ScheduleAssignment::query()
                ->where('schedule_id', $schedule->id)
                ->whereNotIn('schedule_role_id', $submittedRoleIds)
                ->delete();
        });

        return redirect()->back();
    }
}
