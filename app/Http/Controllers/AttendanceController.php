<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Save attendance for an activity (bulk upsert).
     */
    public function store(Request $request, Activity $activity): RedirectResponse
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
                ],
            );
        }

        return to_route('activities.show', $activity);
    }
}
