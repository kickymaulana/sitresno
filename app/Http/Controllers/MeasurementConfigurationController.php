<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeasurementConfigurationRequest;
use App\Http\Requests\UpdateMeasurementConfigurationRequest;
use App\Models\Customer;
use App\Models\MeasurementCondition;
use App\Models\MeasurementConfiguration;
use App\Models\MeasurementConfigurationField;
use App\Models\ModelSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MeasurementConfigurationController extends Controller
{
    public function index(): Response
    {
        $configurations = MeasurementConfiguration::query()
            ->with(['customer', 'modelSize'])
            ->withCount('fields')
            ->latest('id')
            ->get()
            ->map(fn (MeasurementConfiguration $configuration): array => [
                'id' => $configuration->id,
                'customer' => $configuration->customer->name,
                'modelSize' => $configuration->modelSize->displayName(),
                'fieldCount' => $configuration->fields_count,
                'isActive' => $configuration->is_active,
            ]);

        return Inertia::render('MeasurementConfigurations/Index', ['measurementConfigurations' => $configurations]);
    }

    public function create(): Response
    {
        return Inertia::render('MeasurementConfigurations/Create', $this->options());
    }

    public function store(StoreMeasurementConfigurationRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $data = $request->validated();
            $configuration = MeasurementConfiguration::query()->create([
                'customer_id' => $data['customerId'],
                'model_size_id' => $data['modelSizeId'],
                'is_active' => $data['isActive'],
            ]);
            $this->synchronizeFields($configuration, $data['fields']);
        });

        return to_route('measurement-configurations.index')->with('status', 'Konfigurasi pengukuran berhasil ditambahkan.');
    }

    public function edit(MeasurementConfiguration $measurementConfiguration): Response
    {
        $measurementConfiguration->load('fields');

        return Inertia::render('MeasurementConfigurations/Edit', $this->options() + [
            'measurementConfiguration' => $this->transform($measurementConfiguration),
        ]);
    }

    public function update(UpdateMeasurementConfigurationRequest $request, MeasurementConfiguration $measurementConfiguration): RedirectResponse
    {
        DB::transaction(function () use ($request, $measurementConfiguration): void {
            $data = $request->validated();
            $measurementConfiguration->update([
                'customer_id' => $data['customerId'],
                'model_size_id' => $data['modelSizeId'],
                'is_active' => $data['isActive'],
            ]);
            $this->synchronizeFields($measurementConfiguration, $data['fields']);
        });

        return to_route('measurement-configurations.index')->with('status', 'Konfigurasi pengukuran berhasil diperbarui.');
    }

    public function destroy(MeasurementConfiguration $measurementConfiguration): RedirectResponse
    {
        if ($measurementConfiguration->formPengukuranItems()->exists()) {
            return to_route('measurement-configurations.index')
                ->with('error', 'Konfigurasi sudah digunakan pada form pengukuran dan tidak dapat dihapus. Nonaktifkan konfigurasi jika tidak digunakan lagi.');
        }

        $measurementConfiguration->delete();

        return to_route('measurement-configurations.index')->with('status', 'Konfigurasi pengukuran berhasil dihapus.');
    }

    private function synchronizeFields(MeasurementConfiguration $configuration, array $fields): void
    {
        $retainedIds = [];

        foreach ($fields as $field) {
            $configurationField = $configuration->fields()->updateOrCreate(
                [
                    'parameter_name' => trim((string) $field['parameterName']),
                    'condition' => $field['condition'],
                ],
                [
                    'pdf_parameter_name' => $field['pdfParameterName'] ?? null,
                    'expected_value' => $field['expectedValue'] ?? null,
                    'description' => $field['description'] ?? null,
                    'sort_order' => $field['sortOrder'],
                    'is_active' => $field['isActive'],
                ],
            );
            $retainedIds[] = $configurationField->id;
        }

        $fieldsToRemove = $configuration->fields()->whereNotIn('id', $retainedIds);

        (clone $fieldsToRemove)
            ->whereHas('actuals')
            ->update(['is_active' => false]);

        $fieldsToRemove
            ->whereDoesntHave('actuals')
            ->delete();
    }

    private function options(): array
    {
        return [
            'customers' => Customer::query()->orderBy('name')->get()->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
            ]),
            'modelSizes' => ModelSize::query()->orderByRaw('COALESCE(name, full_name, model)')->get()->map(fn (ModelSize $modelSize): array => [
                'id' => $modelSize->id,
                'name' => $modelSize->displayName(),
            ]),
            'measurementConditions' => MeasurementCondition::query()->where('is_active', true)->orderBy('sort_order')->get()->map(fn (MeasurementCondition $condition): array => [
                'id' => $condition->id,
                'code' => $condition->code,
                'name' => $condition->label,
            ]),
            'copyableConfigurations' => MeasurementConfiguration::query()
                ->with(['customer', 'modelSize', 'fields' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
                ->latest('id')
                ->get()
                ->map(fn (MeasurementConfiguration $configuration): array => [
                    'id' => $configuration->id,
                    'customer' => $configuration->customer->name,
                    'modelSize' => $configuration->modelSize->displayName(),
                    'fields' => $configuration->fields->map(fn (MeasurementConfigurationField $field): array => [
                        'id' => $field->id,
                        'parameterName' => $field->parameter_name,
                        'pdfParameterName' => $field->pdf_parameter_name,
                        'condition' => $field->condition,
                        'expectedValue' => $field->expected_value,
                        'description' => $field->description,
                        'sortOrder' => $field->sort_order,
                        'isActive' => $field->is_active,
                    ])->values(),
                ])->values(),
        ];
    }

    private function transform(MeasurementConfiguration $configuration): array
    {
        return [
            'id' => $configuration->id,
            'customerId' => $configuration->customer_id,
            'modelSizeId' => $configuration->model_size_id,
            'isActive' => $configuration->is_active,
            'fields' => $configuration->fields->map(fn (MeasurementConfigurationField $field): array => [
                'id' => $field->id,
                'parameterName' => $field->parameter_name,
                'pdfParameterName' => $field->pdf_parameter_name,
                'condition' => $field->condition,
                'expectedValue' => $field->expected_value,
                'description' => $field->description,
                'sortOrder' => $field->sort_order,
                'isActive' => $field->is_active,
            ])->values(),
        ];
    }
}
