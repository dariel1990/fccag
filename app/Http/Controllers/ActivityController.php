<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        return Inertia::render('activities/Index', [
            'activities' => Activity::query()
                ->with('activityType')
                ->withCount([
                    'attendances',
                    'attendances as present_count' => fn ($query) => $query->where('is_present', true),
                ])
                ->whereYear('activity_date', $year)
                ->whereMonth('activity_date', $month)
                ->orderBy('activity_date')
                ->get()
                ->map(fn (Activity $activity) => [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'activity_type' => $activity->activityType->name,
                    'activity_date' => $activity->activity_date->format('Y-m-d'),
                    'attendances_count' => $activity->attendances_count,
                    'present_count' => $activity->present_count,
                ]),
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('activities/Create', [
            'activityTypes' => ActivityType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $activity = Activity::create($request->validated());

        return to_route('activities.show', $activity);
    }

    /**
     * Display the specified resource with attendance management.
     */
    public function show(Activity $activity): Response
    {
        $activity->load('activityType.departments');

        $departmentIds = $activity->activityType->departments->pluck('id');

        $query = Participant::query()
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($departmentIds->isNotEmpty()) {
            $query->whereIn('department_id', $departmentIds);
        }

        $participants = $query->get();

        $attendances = $activity->attendances()
            ->get()
            ->keyBy('person_id');

        return Inertia::render('activities/Show', [
            'activity' => [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'activity_type' => $activity->activityType->name,
                'activity_date' => $activity->activity_date->format('Y-m-d'),
                'departments' => $activity->activityType->departments->pluck('name'),
            ],
            'participants' => $participants->map(fn (Participant $participant) => [
                'id' => $participant->id,
                'full_name' => $participant->full_name,
                'is_present' => $attendances->has($participant->id)
                    ? $attendances->get($participant->id)->is_present
                    : false,
                'remarks' => $attendances->has($participant->id)
                    ? $attendances->get($participant->id)->remarks
                    : null,
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity): Response
    {
        return Inertia::render('activities/Edit', [
            'activity' => [
                'id' => $activity->id,
                'activity_type_id' => $activity->activity_type_id,
                'title' => $activity->title,
                'description' => $activity->description,
                'activity_date' => $activity->activity_date->format('Y-m-d'),
            ],
            'activityTypes' => ActivityType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        $activity->update($request->validated());

        return to_route('activities.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return to_route('activities.index');
    }
}
