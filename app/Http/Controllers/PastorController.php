<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pastor\StorePastorRequest;
use App\Http\Requests\Pastor\UpdatePastorRequest;
use App\Models\Pastor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PastorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('pastors/Index', [
            'pastors' => Pastor::query()->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('pastors/Create');
    }

    public function store(StorePastorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pastors', 'public');
        }

        Pastor::create($data);

        return to_route('pastors.index');
    }

    public function edit(Pastor $pastor): Response
    {
        return Inertia::render('pastors/Edit', [
            'pastor' => $pastor,
        ]);
    }

    public function update(UpdatePastorRequest $request, Pastor $pastor): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($pastor->photo_path) {
                Storage::disk('public')->delete($pastor->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('pastors', 'public');
        }

        $pastor->update($data);

        return to_route('pastors.index');
    }

    public function destroy(Pastor $pastor): RedirectResponse
    {
        if ($pastor->photo_path) {
            Storage::disk('public')->delete($pastor->photo_path);
        }

        $pastor->delete();

        return to_route('pastors.index');
    }
}
