<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Get attendance for an activity.
     */
    public function index(Activity $activity): JsonResponse
    {
        $activity->load('activityType.departments');

        $attendances = $activity->attendances()
            ->with('participant')
            ->get();

        $departmentIds = $activity->activityType->departments->pluck('id');

        $query = Participant::where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($departmentIds->isNotEmpty()) {
            $query->whereIn('department_id', $departmentIds);
        }

        $participants = $query->get();

        return response()->json([
            'activity' => [
                'id' => $activity->id,
                'title' => $activity->title,
                'activity_date' => $activity->activity_date->format('Y-m-d'),
                'departments' => $activity->activityType->departments->pluck('name'),
            ],
            'participants' => $participants->map(fn ($participant) => [
                'id' => $participant->id,
                'full_name' => $participant->full_name,
                'attendance' => $attendances->firstWhere('person_id', $participant->id),
            ]),
            'attendances' => AttendanceResource::collection($attendances),
        ]);
    }

    /**
     * Store or update attendance for an activity.
     */
    public function store(Request $request, Activity $activity): JsonResponse
    {
        $validated = $request->validate([
            'attendances' => ['required', 'array'],
            'attendances.*.person_id' => ['required', 'exists:people,id'],
            'attendances.*.is_present' => ['required', 'boolean'],
            'attendances.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated['attendances'] as $record) {
            Attendance::updateOrCreate(
                [
                    'activity_id' => $activity->id,
                    'person_id' => $record['person_id'],
                ],
                [
                    'is_present' => $record['is_present'],
                    'remarks' => $record['remarks'] ?? null,
                ]
            );
        }

        return response()->json([
            'message' => 'Attendance saved successfully',
        ]);
    }
}
