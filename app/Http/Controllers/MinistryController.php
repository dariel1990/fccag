<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ministry\StoreMinistryRequest;
use App\Http\Requests\Ministry\UpdateMinistryRequest;
use App\Models\Ministry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MinistryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ministries/Index', [
            'ministries' => Ministry::query()->withCount('people')->latest()->get(),
        ]);
    }

    public function store(StoreMinistryRequest $request): RedirectResponse
    {
        Ministry::create($request->validated());

        return to_route('ministries.index');
    }

    public function update(UpdateMinistryRequest $request, Ministry $ministry): RedirectResponse
    {
        $ministry->update($request->validated());

        return to_route('ministries.index');
    }

    public function destroy(Ministry $ministry): RedirectResponse
    {
        $ministry->delete();

        return to_route('ministries.index');
    }
}
