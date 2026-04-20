<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityType\StoreActivityTypeRequest;
use App\Http\Requests\ActivityType\UpdateActivityTypeRequest;
use App\Models\ActivityType;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ActivityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('activity-types/Index', [
            'activityTypes' => ActivityType::query()
                ->withCount('activities')
                ->with('departments:id,name')
                ->latest()
                ->get()
                ->map(fn (ActivityType $activityType) => [
                    'id' => $activityType->id,
                    'name' => $activityType->name,
                    'description' => $activityType->description,
                    'is_active' => $activityType->is_active,
                    'activities_count' => $activityType->activities_count,
                    'departments' => $activityType->departments->map(fn ($d) => ['id' => $d->id, 'name' => $d->name]),
                    'department_ids' => $activityType->departments->pluck('id'),
                ]),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $departmentIds = $validated['department_ids'] ?? [];
        unset($validated['department_ids']);

        $activityType = ActivityType::create($validated);
        $activityType->departments()->sync($departmentIds);

        return to_route('activity-types.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityTypeRequest $request, ActivityType $activityType): RedirectResponse
    {
        $validated = $request->validated();
        $departmentIds = $validated['department_ids'] ?? [];
        unset($validated['department_ids']);

        $activityType->update($validated);
        $activityType->departments()->sync($departmentIds);

        return to_route('activity-types.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityType $activityType): RedirectResponse
    {
        $activityType->delete();

        return to_route('activity-types.index');
    }
}
