<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMasterMouldRequest;
use App\Http\Requests\UpdateMasterMouldRequest;
use App\Models\MasterMould;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MasterMouldController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('MasterMoulds/Index', ['masterMoulds' => MasterMould::query()->orderBy('code')->get()->map(fn (MasterMould $masterMould): array => $this->transform($masterMould))]);
    }

    public function create(): Response
    {
        return Inertia::render('MasterMoulds/Create');
    }

    public function store(StoreMasterMouldRequest $request): RedirectResponse
    {
        MasterMould::query()->create($request->validated());

        return to_route('master-moulds.index');
    }

    public function edit(MasterMould $masterMould): Response
    {
        return Inertia::render('MasterMoulds/Edit', ['masterMould' => $this->transform($masterMould)]);
    }

    public function update(UpdateMasterMouldRequest $request, MasterMould $masterMould): RedirectResponse
    {
        $masterMould->update($request->validated());

        return to_route('master-moulds.index');
    }

    public function destroy(MasterMould $masterMould): RedirectResponse
    {
        $masterMould->delete();

        return to_route('master-moulds.index');
    }

    private function transform(MasterMould $masterMould): array
    {
        return ['id' => $masterMould->id, 'code' => $masterMould->code, 'description' => $masterMould->description];
    }
}
