<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormPengukuranRequest;
use App\Http\Requests\UpdateFormPengukuranRequest;
use App\Models\Area;
use App\Models\Customer;
use App\Models\FormPengukuran;
use App\Models\FormPengukuranHasil;
use App\Models\MasterMould;
use App\Models\ModelSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FormPengukuranController extends Controller
{
    public function index(): Response
    {
        $forms = FormPengukuran::query()
            ->with(['area', 'masterMould', 'modelSize', 'customer'])
            ->latest('id')
            ->get()
            ->map(fn (FormPengukuran $formPengukuran): array => $this->transform($formPengukuran));

        return Inertia::render('FormPengukurans/Index', ['formPengukurans' => $forms]);
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
            $this->synchronizeResults($formPengukuran, $data['results']);

            return $formPengukuran;
        });

        return to_route('form-pengukurans.show', $formPengukuran);
    }

    public function show(FormPengukuran $formPengukuran): Response
    {
        $formPengukuran->load(['area', 'masterMould', 'modelSize', 'customer', 'hasils']);

        return Inertia::render('FormPengukurans/Show', ['formPengukuran' => $this->transform($formPengukuran, true)]);
    }

    public function edit(FormPengukuran $formPengukuran): Response
    {
        $formPengukuran->load(['area', 'masterMould', 'modelSize', 'customer', 'hasils']);

        return Inertia::render('FormPengukurans/Edit', $this->options() + ['formPengukuran' => $this->transform($formPengukuran, true)]);
    }

    public function update(UpdateFormPengukuranRequest $request, FormPengukuran $formPengukuran): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $formPengukuran): void {
            $formPengukuran->update($this->formAttributes($data));
            $this->synchronizeResults($formPengukuran, $data['results']);
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
            'model_size_id' => $data['modelSizeId'],
            'customer_id' => $data['customerId'],
            'hari_ke' => $data['hariKe'] ?? null,
            'mesin' => $data['mesin'] ?? null,
            'tanggal_output_sample' => $data['tanggalOutputSample'] ?? null,
            'tanggal_pengukuran' => $data['tanggalPengukuran'] ?? null,
            'tanggal_naik_mould' => $data['tanggalNaikMould'] ?? null,
            'dibuat_oleh' => $data['dibuatOleh'] ?? null,
            'diperiksa_oleh' => $data['diperiksaOleh'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function synchronizeResults(FormPengukuran $formPengukuran, array $results): void
    {
        $conditions = collect($results)->pluck('condition')->all();
        $formPengukuran->hasils()->whereNotIn('kondisi', $conditions)->delete();

        foreach ($results as $result) {
            $attributes = ['remarks' => $result['remarks'] ?? null];

            foreach (FormPengukuranHasil::MEASUREMENTS as $measurement) {
                $attributes[$measurement] = $result[$measurement] ?? null;
            }

            $formPengukuran->hasils()->updateOrCreate(['kondisi' => $result['condition']], $attributes);
        }
    }

    private function options(): array
    {
        return [
            'areas' => Area::query()->orderBy('name')->get()->map(fn (Area $area): array => ['id' => $area->id, 'name' => $area->name]),
            'masterMoulds' => MasterMould::query()->orderBy('code')->get()->map(fn (MasterMould $masterMould): array => ['id' => $masterMould->id, 'name' => $masterMould->code]),
            'modelSizes' => ModelSize::query()->orderBy('full_name')->get()->map(fn (ModelSize $modelSize): array => ['id' => $modelSize->id, 'name' => $modelSize->full_name]),
            'customers' => Customer::query()->orderBy('name')->get()->map(fn (Customer $customer): array => ['id' => $customer->id, 'name' => $customer->name]),
        ];
    }

    private function transform(FormPengukuran $formPengukuran, bool $withResults = false): array
    {
        $data = [
            'id' => $formPengukuran->id,
            'areaId' => $formPengukuran->area_id,
            'masterMouldId' => $formPengukuran->master_mould_id,
            'modelSizeId' => $formPengukuran->model_size_id,
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
            'modelSize' => $formPengukuran->relationLoaded('modelSize') ? ['id' => $formPengukuran->modelSize->id, 'name' => $formPengukuran->modelSize->full_name] : null,
            'customer' => $formPengukuran->relationLoaded('customer') ? ['id' => $formPengukuran->customer->id, 'name' => $formPengukuran->customer->name] : null,
        ];

        if ($withResults) {
            $data['results'] = $formPengukuran->hasils->map(fn (FormPengukuranHasil $hasil): array => $this->transformResult($hasil))->values();
        }

        return $data;
    }

    private function transformResult(FormPengukuranHasil $hasil): array
    {
        $data = ['id' => $hasil->id, 'condition' => $hasil->kondisi, 'remarks' => $hasil->remarks];

        foreach (FormPengukuranHasil::MEASUREMENTS as $measurement) {
            $data[$measurement] = $hasil->{$measurement};
        }

        return $data;
    }
}
