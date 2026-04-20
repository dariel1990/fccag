<?php

namespace App\Http\Controllers\Si;

use App\Http\Controllers\Controller;
use App\Http\Requests\Si\StoreSiActivityRequest;
use App\Http\Requests\Si\UpdateSiActivityRequest;
use App\Models\Activity;
use App\Models\SiActivity;
use App\Models\SiActivityCategory;
use App\Models\SiAttendance;
use App\Models\SiMember;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiActivityController extends Controller
{
    public function index(): Response
    {
        $activities = SiActivity::query()
            ->with(['category:id,name', 'assignedMembers.caregiver:id,first_name,last_name'])
            ->withCount('assignedMembers')
            ->orderByDesc('conducted_at')
            ->get();

        $allAttendances = SiAttendance::query()
            ->whereIn('si_activity_id', $activities->pluck('id'))
            ->get()
            ->groupBy('si_activity_id');

        return Inertia::render('si/activities/Index', [
            'categories' => SiActivityCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'members' => SiMember::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'churchActivities' => Activity::query()->orderByDesc('activity_date')->get(['id', 'title', 'activity_date']),
            'attendanceStatuses' => collect(\App\Enums\SiAttendanceStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
            'activities' => $activities->map(function (SiActivity $activity) use ($allAttendances) {
                $activityAttendances = $allAttendances->get($activity->id, collect())->keyBy('si_member_id');

                return [
                    'id' => $activity->id,
                    'si_activity_category_id' => $activity->si_activity_category_id,
                    'activity_id' => $activity->activity_id,
                    'title' => $activity->title,
                    'category' => $activity->category?->name,
                    'speaker' => $activity->speaker,
                    'topic' => $activity->topic,
                    'memory_verse' => $activity->memory_verse,
                    'conducted_at' => $activity->conducted_at->toDateString(),
                    'assigned_members_count' => $activity->assigned_members_count,
                    'member_ids' => $activity->assignedMembers->pluck('id'),
                    'members' => $activity->assignedMembers->map(fn (SiMember $member) => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'ph_id' => $member->ph_id,
                        'caregiver' => $member->caregiver?->full_name,
                        'attendance' => $activityAttendances->has($member->id)
                            ? [
                                'status' => $activityAttendances[$member->id]->status->value,
                                'remarks' => $activityAttendances[$member->id]->remarks,
                                'recommendation' => $activityAttendances[$member->id]->recommendation,
                            ]
                            : null,
                    ]),
                ];
            }),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('si/activities/Create', [
            'categories' => SiActivityCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'members' => SiMember::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'activities' => Activity::query()->orderByDesc('activity_date')->get(['id', 'title', 'activity_date']),
        ]);
    }

    public function store(StoreSiActivityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $memberIds = $validated['member_ids'];
        unset($validated['member_ids']);

        $activity = SiActivity::create($validated);
        $activity->assignedMembers()->sync($memberIds);

        return to_route('si.activities.index')->with('openActivityId', $activity->id);
    }

    public function show(SiActivity $activity): Response
    {
        $activity->load(['category:id,name', 'assignedMembers.caregiver:id,first_name,last_name']);

        $attendances = SiAttendance::query()
            ->where('si_activity_id', $activity->id)
            ->get()
            ->keyBy('si_member_id');

        $members = $activity->assignedMembers->map(fn (SiMember $member) => [
            'id' => $member->id,
            'name' => $member->name,
            'ph_id' => $member->ph_id,
            'caregiver' => $member->caregiver?->full_name,
            'attendance' => $attendances->has($member->id)
                ? [
                    'status' => $attendances[$member->id]->status->value,
                    'remarks' => $attendances[$member->id]->remarks,
                    'recommendation' => $attendances[$member->id]->recommendation,
                ]
                : null,
        ]);

        return Inertia::render('si/activities/Show', [
            'categories' => SiActivityCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'activeMembers' => SiMember::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'churchActivities' => Activity::query()->orderByDesc('activity_date')->get(['id', 'title', 'activity_date']),
            'siActivity' => [
                'id' => $activity->id,
                'si_activity_category_id' => $activity->si_activity_category_id,
                'activity_id' => $activity->activity_id,
                'title' => $activity->title,
                'category' => $activity->category?->name,
                'speaker' => $activity->speaker,
                'topic' => $activity->topic,
                'memory_verse' => $activity->memory_verse,
                'conducted_at' => $activity->conducted_at->toDateString(),
                'member_ids' => $activity->assignedMembers->pluck('id')->values()->all(),
            ],
            'members' => $members,
            'attendanceStatuses' => collect(\App\Enums\SiAttendanceStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
        ]);
    }

    public function edit(SiActivity $activity): Response
    {
        $activity->load('assignedMembers:id');

        return Inertia::render('si/activities/Edit', [
            'siActivity' => [
                'id' => $activity->id,
                'si_activity_category_id' => $activity->si_activity_category_id,
                'activity_id' => $activity->activity_id,
                'title' => $activity->title,
                'speaker' => $activity->speaker,
                'topic' => $activity->topic,
                'memory_verse' => $activity->memory_verse,
                'conducted_at' => $activity->conducted_at->toDateString(),
                'member_ids' => $activity->assignedMembers->pluck('id'),
            ],
            'categories' => SiActivityCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'members' => SiMember::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'activities' => Activity::query()->orderByDesc('activity_date')->get(['id', 'title', 'activity_date']),
        ]);
    }

    public function update(UpdateSiActivityRequest $request, SiActivity $activity): RedirectResponse
    {
        $validated = $request->validated();
        $memberIds = $validated['member_ids'];
        unset($validated['member_ids']);

        $activity->update($validated);
        $activity->assignedMembers()->sync($memberIds);

        return to_route('si.activities.index')->with('openActivityId', $activity->id);
    }

    public function destroy(SiActivity $activity): RedirectResponse
    {
        $activity->delete();

        return to_route('si.activities.index');
    }
}
