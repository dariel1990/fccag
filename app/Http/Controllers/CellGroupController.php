<?php

namespace App\Http\Controllers;

use App\Http\Requests\CellGroup\StoreCellGroupRequest;
use App\Http\Requests\CellGroup\UpdateCellGroupRequest;
use App\Models\CellGroup;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CellGroupController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('cell-groups/Index', [
            'cellGroups' => CellGroup::query()->withCount('people')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('cell-groups/Create');
    }

    public function store(StoreCellGroupRequest $request): RedirectResponse
    {
        CellGroup::create($request->validated());

        return to_route('cell-groups.index');
    }

    public function edit(CellGroup $cellGroup): Response
    {
        return Inertia::render('cell-groups/Edit', [
            'cellGroup' => $cellGroup,
        ]);
    }

    public function update(UpdateCellGroupRequest $request, CellGroup $cellGroup): RedirectResponse
    {
        $cellGroup->update($request->validated());

        return to_route('cell-groups.index');
    }

    public function destroy(CellGroup $cellGroup): RedirectResponse
    {
        $cellGroup->delete();

        return to_route('cell-groups.index');
    }
}
