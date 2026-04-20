<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('departments/Index', [
            'departments' => Department::query()->withCount(['officers', 'members'])->latest()->get(),
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('departments', 'public');
        }

        Department::create($data);

        return to_route('departments.index');
    }

    public function show(Department $department): Response
    {
        $department->load([
            'officers' => function ($query): void {
                $query->with('person')->orderByRaw('ended_at IS NOT NULL, started_at DESC');
            },
            'members' => function ($query): void {
                $query->orderBy('last_name')->orderBy('first_name');
            },
        ]);

        return Inertia::render('departments/Show', [
            'department' => $department,
            'participants' => Participant::query()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($department->photo_path) {
                Storage::disk('public')->delete($department->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('departments', 'public');
        }

        $department->update($data);

        return to_route('departments.index');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->photo_path) {
            Storage::disk('public')->delete($department->photo_path);
        }

        $department->delete();

        return to_route('departments.index');
    }
}
