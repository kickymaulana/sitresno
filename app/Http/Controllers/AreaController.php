<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AreaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Areas/Index', ['areas' => Area::query()->orderBy('name')->get()->map(fn (Area $area): array => $this->transform($area))]);
    }

    public function create(): Response
    {
        return Inertia::render('Areas/Create');
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        Area::query()->create($request->validated());

        return to_route('areas.index');
    }

    public function edit(Area $area): Response
    {
        return Inertia::render('Areas/Edit', ['area' => $this->transform($area)]);
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $area->update($request->validated());

        return to_route('areas.index');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $area->delete();

        return to_route('areas.index');
    }

    private function transform(Area $area): array
    {
        return ['id' => $area->id, 'name' => $area->name];
    }
}
