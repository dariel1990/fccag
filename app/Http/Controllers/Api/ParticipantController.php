<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreParticipantRequest;
use App\Http\Requests\Participant\UpdateParticipantRequest;
use App\Http\Resources\ParticipantResource;
use App\Models\CellGroup;
use App\Models\Classification;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;

class ParticipantController extends Controller
{
    /**
     * Return dropdown options for the participant form.
     */
    public function formOptions(): JsonResponse
    {
        return response()->json([
            'cell_groups' => CellGroup::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'classifications' => Classification::query()->orderBy('name')->get(['id', 'name', 'code']),
            'ministries' => Ministry::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $query = Participant::query();

        // Search by name
        if (request()->has('search') && request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by status
        if (request()->has('is_active')) {
            $query->where('is_active', request('is_active') === 'true');
        }

        // Filter by gender
        if (request()->has('gender') && request('gender')) {
            $query->where('gender', request('gender'));
        }

        // Filter by cell group
        if (request()->has('cell_group_id') && request('cell_group_id')) {
            $query->where('cell_group_id', request('cell_group_id'));
        }

        // Filter by classification
        if (request()->has('classification_id') && request('classification_id')) {
            $query->where('classification_id', request('classification_id'));
        }

        // Filter by ministry
        if (request()->has('ministry_id') && request('ministry_id')) {
            $query->whereHas('ministries', fn ($q) => $q->where('ministries.id', request('ministry_id')));
        }

        // Filter by department
        if (request()->has('department_id') && request('department_id')) {
            $query->where('department_id', request('department_id'));
        }

        // Filter by birth month
        if (request()->has('birth_month') && request('birth_month')) {
            $query->whereMonth('birthday', request('birth_month'));
        }

        $participants = $query->with(['cellGroup', 'classification', 'ministries', 'department'])->latest()->get();

        return response()->json([
            'data' => ParticipantResource::collection($participants),
            'filter_options' => [
                'cell_groups' => CellGroup::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'classifications' => Classification::query()->orderBy('name')->get(['id', 'name']),
                'ministries' => Ministry::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParticipantRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ministryIds = $validated['ministry_ids'] ?? [];
        unset($validated['ministry_ids']);

        $participant = Participant::create($validated);

        if (! empty($ministryIds)) {
            $participant->ministries()->sync($ministryIds);
        }

        return response()->json([
            'message' => 'Person of God added successfully',
            'data' => new ParticipantResource($participant->load(['cellGroup', 'classification', 'ministries'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Participant $participant): JsonResponse
    {
        $participant->load(['activities' => function ($query) {
            $query->with('activityType')->latest('activity_date');
        }]);

        return response()->json([
            'data' => new ParticipantResource($participant),
            'attendance_history' => $participant->activities->map(fn ($activity) => [
                'id' => $activity->id,
                'title' => $activity->title,
                'activity_type' => $activity->activityType->name,
                'activity_date' => $activity->activity_date->format('Y-m-d'),
                'is_present' => $activity->pivot->is_present,
                'remarks' => $activity->pivot->remarks,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParticipantRequest $request, Participant $participant): JsonResponse
    {
        $validated = $request->validated();
        $ministryIds = $validated['ministry_ids'] ?? [];
        unset($validated['ministry_ids']);

        $participant->update($validated);
        $participant->ministries()->sync($ministryIds);

        return response()->json([
            'message' => 'Person of God updated successfully',
            'data' => new ParticipantResource($participant->load(['cellGroup', 'classification', 'ministries'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participant $participant): JsonResponse
    {
        $participant->delete();

        return response()->json([
            'message' => 'Person of God removed successfully',
        ]);
    }
}
