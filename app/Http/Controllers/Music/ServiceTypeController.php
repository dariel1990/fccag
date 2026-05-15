<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\StoreServiceTypeRequest;
use App\Http\Requests\Music\UpdateServiceTypeRequest;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('music/service-types/Index', [
            'serviceTypes' => ServiceType::query()
                ->orderBy('sort_order')
                ->get(['id', 'name', 'day_of_week', 'color', 'sort_order']),
        ]);
    }

    public function store(StoreServiceTypeRequest $request): RedirectResponse
    {
        ServiceType::create($request->validated());

        return redirect()->back();
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $serviceType->update($request->validated());

        return redirect()->back();
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        $serviceType->delete();

        return to_route('music.service-types.index');
    }
}
