<?php

namespace App\Http\Controllers;

use App\Http\Requests\Classification\StoreClassificationRequest;
use App\Http\Requests\Classification\UpdateClassificationRequest;
use App\Models\Classification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClassificationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('classifications/Index', [
            'classifications' => Classification::query()->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('classifications/Create');
    }

    public function store(StoreClassificationRequest $request): RedirectResponse
    {
        Classification::create($request->validated());

        return to_route('classifications.index');
    }

    public function edit(Classification $classification): Response
    {
        return Inertia::render('classifications/Edit', [
            'classification' => $classification,
        ]);
    }

    public function update(UpdateClassificationRequest $request, Classification $classification): RedirectResponse
    {
        $classification->update($request->validated());

        return to_route('classifications.index');
    }

    public function destroy(Classification $classification): RedirectResponse
    {
        $classification->delete();

        return to_route('classifications.index');
    }
}
