<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityType\StoreActivityTypeRequest;
use App\Http\Requests\ActivityType\UpdateActivityTypeRequest;
use App\Http\Resources\ActivityTypeResource;
use App\Models\ActivityType;
use Illuminate\Http\JsonResponse;

class ActivityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $activityTypes = ActivityType::withCount('activities')
            ->latest()
            ->get();

        return response()->json([
            'data' => ActivityTypeResource::collection($activityTypes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityTypeRequest $request): JsonResponse
    {
        $activityType = ActivityType::create($request->validated());

        return response()->json([
            'message' => 'Activity type created successfully',
            'data' => new ActivityTypeResource($activityType),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityType $activityType): JsonResponse
    {
        return response()->json([
            'data' => new ActivityTypeResource($activityType->loadCount('activities')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityTypeRequest $request, ActivityType $activityType): JsonResponse
    {
        $activityType->update($request->validated());

        return response()->json([
            'message' => 'Activity type updated successfully',
            'data' => new ActivityTypeResource($activityType),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityType $activityType): JsonResponse
    {
        $activityType->delete();

        return response()->json([
            'message' => 'Activity type deleted successfully',
        ]);
    }
}
