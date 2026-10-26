<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeasurementConditionRequest;
use App\Http\Requests\UpdateMeasurementConditionRequest;
use App\Models\MeasurementCondition;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MeasurementConditionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('MeasurementConditions/Index', [
            'conditions' => MeasurementCondition::query()->orderBy('sort_order')->orderBy('label')->get()->map(fn (MeasurementCondition $condition): array => $this->transform($condition)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MeasurementConditions/Create');
    }

    public function store(StoreMeasurementConditionRequest $request): RedirectResponse
    {
        MeasurementCondition::query()->create($request->validated());

        return to_route('measurement-conditions.index');
    }

    public function edit(MeasurementCondition $measurementCondition): Response
    {
        return Inertia::render('MeasurementConditions/Edit', ['condition' => $this->transform($measurementCondition)]);
    }

    public function update(UpdateMeasurementConditionRequest $request, MeasurementCondition $measurementCondition): RedirectResponse
    {
        $measurementCondition->update($request->validated());

        return to_route('measurement-conditions.index');
    }

    public function destroy(MeasurementCondition $measurementCondition): RedirectResponse
    {
        $measurementCondition->update(['is_active' => false]);

        return to_route('measurement-conditions.index');
    }

    private function transform(MeasurementCondition $condition): array
    {
        return [
            'id' => $condition->id,
            'code' => $condition->code,
            'name' => $condition->label,
            'label' => $condition->label,
            'sort_order' => $condition->sort_order,
            'isActive' => $condition->is_active,
        ];
    }
}
