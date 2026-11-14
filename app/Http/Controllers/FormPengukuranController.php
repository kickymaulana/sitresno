<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormPengukuranRequest;
use App\Http\Requests\UpdateFormPengukuranRequest;
use App\Models\Area;
use App\Models\Customer;
use App\Models\FormPengukuran;
use App\Models\FormPengukuranItem;
use App\Models\MasterMould;
use App\Models\MeasurementActual;
use App\Models\MeasurementCondition;
use App\Models\MeasurementConfiguration;
use App\Models\MeasurementConfigurationField;
use App\Models\ModelSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FormPengukuranController extends Controller
{
    public function index(Request $request): Response
    {
        $forms = FormPengukuran::query()
            ->with(['area', 'masterMould', 'customer', 'items.modelSize'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->trim().'%';
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', $search))
                        ->orWhereHas('masterMould', fn ($mouldQuery) => $mouldQuery->where('code', 'like', $search))
                        ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('model_size_name', 'like', $search));
                });
            })
            ->when($request->integer('customerId') > 0, fn ($query) => $query->where('customer_id', $request->integer('customerId')))
            ->when($request->filled('status') && $request->string('status')->toString() !== 'all', fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('hariKe'), fn ($query) => $query->where('hari_ke', 'like', '%'.$request->string('hariKe')->trim().'%'))
            ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal_pengukuran', $request->date('tanggal')))
            ->latest('id')
            ->get()
            ->map(fn (FormPengukuran $formPengukuran): array => $this->transform($formPengukuran));

        return Inertia::render('FormPengukurans/Index', [
            'formPengukurans' => $forms,
            'customers' => Customer::query()->orderBy('name')->get()->map(fn (Customer $customer): array => ['id' => $customer->id, 'name' => $customer->name]),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'customerId' => $request->integer('customerId') ?: null,
                'status' => $request->string('status')->toString(),
                'hariKe' => $request->string('hariKe')->toString(),
                'tanggal' => $request->string('tanggal')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('FormPengukurans/Create', $this->options());
    }

    public function store(StoreFormPengukuranRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $formPengukuran = DB::transaction(function () use ($data): FormPengukuran {
            $formPengukuran = FormPengukuran::query()->create($this->formAttributes($data));
            $this->synchronizeItems($formPengukuran, $data['items']);

            return $formPengukuran;
        });

        return to_route('form-pengukurans.show', $formPengukuran);
    }

    public function show(FormPengukuran $formPengukuran): Response
    {
        $formPengukuran->load($this->relations());

        return Inertia::render('FormPengukurans/Show', [
            'formPengukuran' => $this->transform($formPengukuran, true),
            'measurementConditions' => $this->measurementConditions(),
        ]);
    }

    public function edit(FormPengukuran $formPengukuran): Response
    {
        $formPengukuran->load($this->relations());

        return Inertia::render('FormPengukurans/Edit', $this->options($formPengukuran) + ['formPengukuran' => $this->transform($formPengukuran, true)]);
    }

    public function update(UpdateFormPengukuranRequest $request, FormPengukuran $formPengukuran): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $formPengukuran): void {
            $wasDraft = $formPengukuran->status === 'draft';
            $formPengukuran->update($this->formAttributes($data));
            $this->synchronizeItems($formPengukuran, $data['items'], $wasDraft);
        });

        return to_route('form-pengukurans.show', $formPengukuran);
    }

    public function destroy(FormPengukuran $formPengukuran): RedirectResponse
    {
        $formPengukuran->delete();

        return to_route('form-pengukurans.index');
    }

    private function formAttributes(array $data): array
    {
        return [
            'area_id' => $data['areaId'],
            'master_mould_id' => $data['masterMouldId'],
            'model_size_id' => $data['items'][0]['modelSizeId'],
            'customer_id' => $data['customerId'],
            'hari_ke' => $data['hariKe'] ?? null,
            'mesin' => $data['mesin'] ?? null,
            'tanggal_output_sample' => $data['tanggalOutputSample'] ?? null,
            'tanggal_pengukuran' => $data['tanggalPengukuran'],
            'tanggal_naik_mould' => $data['tanggalNaikMould'] ?? null,
            'dibuat_oleh' => $data['dibuatOleh'] ?? null,
            'diperiksa_oleh' => $data['diperiksaOleh'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function synchronizeItems(FormPengukuran $formPengukuran, array $items, bool $wasDraft = true): void
    {
        $modelSizes = ModelSize::query()->whereIn('id', collect($items)->pluck('modelSizeId'))->get()->keyBy('id');
        $configurations = MeasurementConfiguration::query()
            ->where('customer_id', $formPengukuran->customer_id)
            ->whereIn('model_size_id', collect($items)->pluck('modelSizeId'))
            ->with(['fields' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->keyBy('model_size_id');
        $retainedItemIds = [];

        foreach ($items as $index => $itemData) {
            $modelSize = $modelSizes->get((int) $itemData['modelSizeId']);
            $configuration = $configurations->get($modelSize->id);
            $existingItem = $formPengukuran->items()->with('actuals')->where('model_size_id', $modelSize->id)->first();
            $preservesSnapshot = ! $wasDraft
                && $existingItem?->measurement_configuration_id === $configuration->id;
            $item = $formPengukuran->items()->updateOrCreate(
                ['model_size_id' => $modelSize->id],
                [
                    'measurement_configuration_id' => $configuration->id,
                    'model_size_name' => $modelSize->displayName(),
                    'remarks' => $itemData['remarks'] ?? null,
                    'sort_order' => $index + 1,
                ],
            );
            $retainedItemIds[] = $item->id;

            if ($preservesSnapshot) {
                $this->updateSnapshotActualValues($item, $itemData['actuals']);
            } else {
                $this->synchronizeActuals($item, $configuration, $itemData['actuals']);
            }
        }

        $formPengukuran->items()->whereNotIn('id', $retainedItemIds)->delete();
    }

    private function updateSnapshotActualValues(FormPengukuranItem $item, array $submittedActuals): void
    {
        $actualValues = collect($submittedActuals)->keyBy('configurationFieldId');

        foreach ($item->actuals as $actual) {
            $actual->update([
                'actual' => $actualValues->get($actual->measurement_configuration_field_id)['actual'] ?? null,
            ]);
        }
    }

    private function synchronizeActuals(FormPengukuranItem $item, MeasurementConfiguration $configuration, array $submittedActuals): void
    {
        $actualValues = collect($submittedActuals)->keyBy('configurationFieldId');
        $retainedActualIds = [];

        foreach ($configuration->fields as $configurationField) {
            $actual = $item->actuals()->updateOrCreate(
                ['measurement_configuration_field_id' => $configurationField->id],
                [
                    'condition' => $configurationField->condition,
                    'specification' => $configurationField->expected_value,
                    'actual' => $actualValues->get($configurationField->id)['actual'] ?? null,
                    'parameter_label' => $configurationField->parameter_name,
                    'parameter_description' => $configurationField->description,
                    'parameter_sort_order' => $configurationField->sort_order,
                    'parameter_is_starred' => false,
                ],
            );
            $retainedActualIds[] = $actual->id;
        }

        $item->actuals()->whereNotIn('id', $retainedActualIds)->delete();
    }

    private function options(?FormPengukuran $formPengukuran = null): array
    {
        $configurations = MeasurementConfiguration::query()
            ->where('is_active', true)
            ->with(['fields' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->map(fn (MeasurementConfiguration $configuration): array => $this->transformConfiguration($configuration));

        if ($formPengukuran !== null) {
            foreach ($formPengukuran->items as $item) {
                if ($item->measurement_configuration_id === null || $formPengukuran->status === 'draft') {
                    continue;
                }

                $configurations = $configurations
                    ->reject(fn (array $configuration): bool => $configuration['customerId'] === $formPengukuran->customer_id && $configuration['modelSizeId'] === $item->model_size_id)
                    ->push([
                        'id' => $item->measurement_configuration_id,
                        'customerId' => $formPengukuran->customer_id,
                        'modelSizeId' => $item->model_size_id,
                        'fields' => $item->actuals->map(fn (MeasurementActual $actual): array => [
                            'id' => $actual->measurement_configuration_field_id,
                            'condition' => $actual->condition,
                            'expectedValue' => $actual->specification,
                            'description' => $actual->parameter_description,
                            'sortOrder' => $actual->parameter_sort_order,
                            'label' => $actual->parameter_label,
                            'isStarred' => $actual->parameter_is_starred,
                        ])->values(),
                    ]);
            }
        }

        return [
            'areas' => Area::query()->orderBy('name')->get()->map(fn (Area $area): array => ['id' => $area->id, 'name' => $area->name]),
            'masterMoulds' => MasterMould::query()->orderBy('code')->get()->map(fn (MasterMould $masterMould): array => ['id' => $masterMould->id, 'name' => $masterMould->code]),
            'modelSizes' => ModelSize::query()->orderByRaw('COALESCE(name, full_name, model)')->get()->map(fn (ModelSize $modelSize): array => [
                'id' => $modelSize->id,
                'name' => $modelSize->displayName(),
            ]),
            'customers' => Customer::query()->orderBy('name')->get()->map(fn (Customer $customer): array => ['id' => $customer->id, 'name' => $customer->name]),
            'measurementConditions' => $this->measurementConditions(),
            'measurementConfigurations' => $configurations->values(),
        ];
    }

    private function measurementConditions(): array
    {
        return MeasurementCondition::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (MeasurementCondition $condition): array => [
                'id' => $condition->id,
                'code' => $condition->code,
                'name' => $condition->label,
            ])
            ->all();
    }

    private function transformConfiguration(MeasurementConfiguration $configuration): array
    {
        return [
            'id' => $configuration->id,
            'customerId' => $configuration->customer_id,
            'modelSizeId' => $configuration->model_size_id,
            'fields' => $configuration->fields->map(fn (MeasurementConfigurationField $field): array => [
                'id' => $field->id,
                'condition' => $field->condition,
                'expectedValue' => $field->expected_value,
                'description' => $field->description,
                'sortOrder' => $field->sort_order,
                'parameterName' => $field->parameter_name,
                'label' => $field->parameter_name,
                'pdfParameterName' => $field->pdf_parameter_name,
                'isStarred' => false,
            ])->values(),
        ];
    }

    private function transform(FormPengukuran $formPengukuran, bool $withItems = false): array
    {
        $data = [
            'id' => $formPengukuran->id,
            'areaId' => $formPengukuran->area_id,
            'masterMouldId' => $formPengukuran->master_mould_id,
            'customerId' => $formPengukuran->customer_id,
            'hariKe' => $formPengukuran->hari_ke,
            'mesin' => $formPengukuran->mesin,
            'tanggalOutputSample' => $formPengukuran->tanggal_output_sample?->toDateString(),
            'tanggalPengukuran' => $formPengukuran->tanggal_pengukuran?->toDateString(),
            'tanggalNaikMould' => $formPengukuran->tanggal_naik_mould?->toDateString(),
            'dibuatOleh' => $formPengukuran->dibuat_oleh,
            'diperiksaOleh' => $formPengukuran->diperiksa_oleh,
            'status' => $formPengukuran->status,
            'area' => $formPengukuran->relationLoaded('area') ? ['id' => $formPengukuran->area->id, 'name' => $formPengukuran->area->name] : null,
            'masterMould' => $formPengukuran->relationLoaded('masterMould') ? ['id' => $formPengukuran->masterMould->id, 'name' => $formPengukuran->masterMould->code] : null,
            'customer' => $formPengukuran->relationLoaded('customer') ? ['id' => $formPengukuran->customer->id, 'name' => $formPengukuran->customer->name] : null,
            'modelSizes' => $formPengukuran->relationLoaded('items') ? $formPengukuran->items->map(fn (FormPengukuranItem $item): array => ['id' => $item->model_size_id, 'name' => $item->model_size_name])->values() : [],
        ];

        if ($withItems) {
            $data['items'] = $formPengukuran->items->map(fn (FormPengukuranItem $item): array => $this->transformItem($item, $formPengukuran->status === 'draft'))->values();
        }

        return $data;
    }

    private function transformItem(FormPengukuranItem $item, bool $useLatestConfiguration = false): array
    {
        $actuals = $item->actuals->keyBy('measurement_configuration_field_id');
        $fields = $useLatestConfiguration
            ? $item->measurementConfiguration?->fields->where('is_active', true) ?? collect()
            : collect();

        return [
            'id' => $item->id,
            'modelSizeId' => $item->model_size_id,
            'modelSize' => ['id' => $item->model_size_id, 'name' => $item->model_size_name],
            'measurementConfigurationId' => $item->measurement_configuration_id,
            'remarks' => $item->remarks,
            'actuals' => ($useLatestConfiguration ? $fields : $item->actuals)->map(function (MeasurementConfigurationField|MeasurementActual $measurement) use ($actuals, $useLatestConfiguration): array {
                $fieldId = $useLatestConfiguration ? $measurement->id : $measurement->measurement_configuration_field_id;
                $actual = $useLatestConfiguration ? $actuals->get($fieldId) : $measurement;

                return [
                    'configurationFieldId' => $fieldId,
                    'condition' => $useLatestConfiguration ? $measurement->condition : $measurement->condition,
                    'specification' => $useLatestConfiguration ? $measurement->expected_value : $measurement->specification,
                    'actual' => $actual?->actual,
                    'label' => $useLatestConfiguration ? $measurement->parameter_name : $measurement->parameter_label,
                    'pdfParameterName' => $useLatestConfiguration && $measurement instanceof MeasurementConfigurationField ? $measurement->pdf_parameter_name : null,
                    'description' => $useLatestConfiguration ? $measurement->description : $measurement->parameter_description,
                    'sortOrder' => $useLatestConfiguration ? $measurement->sort_order : $measurement->parameter_sort_order,
                    'isStarred' => $useLatestConfiguration ? false : $measurement->parameter_is_starred,
                ];
            })->values(),
        ];
    }

    private function relations(): array
    {
        return ['area', 'masterMould', 'customer', 'items.modelSize', 'items.actuals', 'items.measurementConfiguration.fields'];
    }
}
