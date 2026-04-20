<?php

namespace App\Http\Controllers\Si;

use App\Http\Controllers\Controller;
use App\Http\Requests\Si\StoreSiAttendanceRequest;
use App\Models\SiActivity;
use App\Models\SiAttendance;
use Illuminate\Http\RedirectResponse;

class SiAttendanceController extends Controller
{
    public function store(StoreSiAttendanceRequest $request, SiActivity $siActivity): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated['attendances'] as $record) {
            SiAttendance::updateOrCreate(
                [
                    'si_activity_id' => $siActivity->id,
                    'si_member_id' => $record['si_member_id'],
                ],
                [
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'recommendation' => $record['recommendation'] ?? null,
                ],
            );
        }

        return back()->with('success', 'Attendance saved successfully.');
    }
}
