<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\StoreScheduleRoleRequest;
use App\Http\Requests\Music\UpdateScheduleRoleRequest;
use App\Models\ScheduleRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleRoleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('music/schedule-roles/Index', [
            'roles' => ScheduleRole::query()
                ->withCount('assignments')
                ->orderBy('team')
                ->orderBy('sort_order')
                ->get(['id', 'team', 'name', 'sort_order']),
        ]);
    }

    public function store(StoreScheduleRoleRequest $request): RedirectResponse
    {
        ScheduleRole::create($request->validated());

        return redirect()->back();
    }

    public function update(UpdateScheduleRoleRequest $request, ScheduleRole $scheduleRole): RedirectResponse
    {
        $scheduleRole->update($request->validated());

        return redirect()->back();
    }

    public function destroy(ScheduleRole $scheduleRole): RedirectResponse
    {
        $scheduleRole->delete();

        return to_route('music.schedule-roles.index');
    }
}
