<?php

namespace App\Http\Controllers\Si;

use App\Http\Controllers\Controller;
use App\Http\Requests\Si\StoreSiActivityCategoryRequest;
use App\Http\Requests\Si\UpdateSiActivityCategoryRequest;
use App\Models\SiActivityCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiActivityCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('si/activity-categories/Index', [
            'categories' => SiActivityCategory::query()
                ->withCount('siActivities')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('si/activity-categories/Create');
    }

    public function store(StoreSiActivityCategoryRequest $request): RedirectResponse
    {
        SiActivityCategory::create($request->validated());

        return to_route('si.activity-categories.index');
    }

    public function edit(SiActivityCategory $activityCategory): Response
    {
        return Inertia::render('si/activity-categories/Edit', [
            'category' => $activityCategory,
        ]);
    }

    public function update(UpdateSiActivityCategoryRequest $request, SiActivityCategory $activityCategory): RedirectResponse
    {
        $activityCategory->update($request->validated());

        return to_route('si.activity-categories.index');
    }

    public function destroy(SiActivityCategory $activityCategory): RedirectResponse
    {
        $activityCategory->delete();

        return to_route('si.activity-categories.index');
    }
}
