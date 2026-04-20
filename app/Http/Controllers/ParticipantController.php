<?php

namespace App\Http\Controllers;

use App\Http\Requests\Participant\StoreParticipantRequest;
use App\Http\Requests\Participant\UpdateParticipantRequest;
use App\Models\CellGroup;
use App\Models\Classification;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('participants/Index', [
            'participants' => Participant::query()
                ->with(['cellGroup', 'classification', 'ministries', 'department'])
                ->latest()
                ->get()
                ->map(fn (Participant $participant) => [
                    'id' => $participant->id,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'full_name' => $participant->full_name,
                    'gender' => $participant->gender->value,
                    'birthday' => $participant->birthday?->format('Y-m-d'),
                    'contact_number' => $participant->contact_number,
                    'cell_group_id' => $participant->cell_group_id,
                    'cell_group' => $participant->cellGroup?->name,
                    'classification_id' => $participant->classification_id,
                    'classification' => $participant->classification?->name,
                    'department_id' => $participant->department_id,
                    'department' => $participant->department?->name,
                    'ministry_ids' => $participant->ministries->pluck('id'),
                    'ministries' => $participant->ministries->pluck('name'),
                    'date_joined' => $participant->date_joined->format('Y-m-d'),
                    'is_active' => $participant->is_active,
                ]),
            'cellGroups' => CellGroup::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'classifications' => Classification::query()->orderBy('name')->get(['id', 'name', 'code']),
            'ministries' => Ministry::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParticipantRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $ministryIds = $validated['ministry_ids'] ?? [];
        unset($validated['ministry_ids']);

        $participant = Participant::create($validated);

        if (! empty($ministryIds)) {
            $participant->ministries()->sync($ministryIds);
        }

        return to_route('participants.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Participant $participant): Response|JsonResponse
    {
        $participant->load([
            'cellGroup',
            'classification',
            'department',
            'ministries',
            'activities' => function ($query) {
                $query->with('activityType')->latest('activity_date');
            },
        ]);

        $participantData = [
            'id' => $participant->id,
            'first_name' => $participant->first_name,
            'last_name' => $participant->last_name,
            'full_name' => $participant->full_name,
            'gender' => $participant->gender->value,
            'birthday' => $participant->birthday?->format('Y-m-d'),
            'contact_number' => $participant->contact_number,
            'address' => $participant->address,
            'cell_group' => $participant->cellGroup?->name,
            'classification' => $participant->classification?->name,
            'department' => $participant->department?->name,
            'ministries' => $participant->ministries->pluck('name'),
            'date_joined' => $participant->date_joined->format('Y-m-d'),
            'is_active' => $participant->is_active,
        ];

        $attendanceHistory = $participant->activities->map(fn ($activity) => [
            'id' => $activity->id,
            'title' => $activity->title,
            'activity_type' => $activity->activityType->name,
            'activity_date' => $activity->activity_date->format('Y-m-d'),
            'is_present' => $activity->pivot->is_present,
            'remarks' => $activity->pivot->remarks,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'data' => $participantData,
                'attendance_history' => $attendanceHistory,
            ]);
        }

        return Inertia::render('participants/Show', [
            'participant' => $participantData,
            'attendanceHistory' => $attendanceHistory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParticipantRequest $request, Participant $participant): RedirectResponse
    {
        $validated = $request->validated();
        $ministryIds = $validated['ministry_ids'] ?? [];
        unset($validated['ministry_ids']);

        $participant->update($validated);
        $participant->ministries()->sync($ministryIds);

        return to_route('participants.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participant $participant): RedirectResponse
    {
        $participant->delete();

        return to_route('participants.index');
    }
}
