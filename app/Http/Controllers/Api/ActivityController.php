<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $activities = Activity::with('activityType')
            ->withCount([
                'attendances',
                'attendances as present_count' => fn ($query) => $query->where('is_present', true),
            ])
            ->whereYear('activity_date', $year)
            ->whereMonth('activity_date', $month)
            ->orderBy('activity_date')
            ->get();

        return response()->json([
            'data' => ActivityResource::collection($activities),
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $activity = Activity::create($request->validated());

        return response()->json([
            'message' => 'Activity created successfully',
            'data' => new ActivityResource($activity->load('activityType')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity): JsonResponse
    {
        $activity->load('activityType');

        return response()->json([
            'data' => new ActivityResource($activity),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityRequest $request, Activity $activity): JsonResponse
    {
        $activity->update($request->validated());

        return response()->json([
            'message' => 'Activity updated successfully',
            'data' => new ActivityResource($activity->load('activityType')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $activity->delete();

        return response()->json([
            'message' => 'Activity deleted successfully',
        ]);
    }
}
