<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModelSizeRequest;
use App\Http\Requests\UpdateModelSizeRequest;
use App\Models\ModelSize;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ModelSizeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ModelSizes/Index', ['modelSizes' => ModelSize::query()->orderBy('model')->orderBy('size')->get()->map(fn (ModelSize $modelSize): array => $this->transform($modelSize))]);
    }

    public function create(): Response
    {
        return Inertia::render('ModelSizes/Create');
    }

    public function store(StoreModelSizeRequest $request): RedirectResponse
    {
        ModelSize::query()->create($this->attributes($request->validated()));

        return to_route('model-sizes.index');
    }

    public function edit(ModelSize $modelSize): Response
    {
        return Inertia::render('ModelSizes/Edit', ['modelSize' => $this->transform($modelSize)]);
    }

    public function update(UpdateModelSizeRequest $request, ModelSize $modelSize): RedirectResponse
    {
        $modelSize->update($this->attributes($request->validated()));

        return to_route('model-sizes.index');
    }

    public function destroy(ModelSize $modelSize): RedirectResponse
    {
        $modelSize->delete();

        return to_route('model-sizes.index');
    }

    private function attributes(array $data): array
    {
        return ['model' => $data['model'], 'size' => $data['size'], 'full_name' => $data['fullName'], 'description' => $data['description'] ?? null];
    }

    private function transform(ModelSize $modelSize): array
    {
        return ['id' => $modelSize->id, 'model' => $modelSize->model, 'size' => $modelSize->size, 'fullName' => $modelSize->full_name, 'description' => $modelSize->description];
    }
}
