<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Customer;
use App\Models\FormPengukuran;
use App\Models\FormPengukuranHasil;
use App\Models\MasterMould;
use App\Models\ModelSize;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class FormPengukuranCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_and_unapproved_users_cannot_view_records(): void
    {
        $this->get(route('form-pengukurans.index'))->assertRedirect(route('login'));

        $unapproved = User::factory()->create(['is_approved' => false]);
        $this->actingAs($unapproved)->get(route('form-pengukurans.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_record_permission_is_forbidden(): void
    {
        $user = User::factory()->create(['is_approved' => true]);

        $this->actingAs($user)->get(route('form-pengukurans.index'))->assertForbidden();
        $this->actingAs($user)->post(route('form-pengukurans.store'), $this->payload())->assertForbidden();
    }

    public function test_record_routes_enforce_permissions_by_action(): void
    {
        $supervisor = User::factory()->create(['is_approved' => true]);
        $supervisor->assignRole('supervisor');
        $form = FormPengukuran::factory()->create();

        $this->actingAs($supervisor)->get(route('form-pengukurans.index'))->assertOk();
        $this->actingAs($supervisor)->get(route('form-pengukurans.create'))->assertForbidden();
        $this->actingAs($supervisor)->delete(route('form-pengukurans.destroy', $form))->assertForbidden();
    }

    public function test_inspector_can_store_parent_and_exactly_three_results(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->inspector())
            ->post(route('form-pengukurans.store'), $payload)
            ->assertRedirect();

        $form = FormPengukuran::query()->firstOrFail();
        $this->assertSame('menunggu_pemeriksaan', $form->status);
        $this->assertSame('7', $form->hari_ke);
        $this->assertSame('2026-10-15', $form->tanggal_pengukuran->toDateString());
        $this->assertCount(3, $form->hasils);
        $this->assertEqualsCanonicalizing(FormPengukuranHasil::CONDITIONS, $form->hasils->pluck('kondisi')->all());
        $this->assertDatabaseHas('form_pengukuran_hasils', [
            'form_pengukuran_id' => $form->id,
            'kondisi' => 'sebelum_cuci',
            'h' => 12.345,
            'sl_titik' => 9.876,
        ]);
        $this->assertFalse(Arr::has($form->hasils->first()->getAttributes(), 'sl_titik_70mm'));
    }

    public function test_nested_validation_requires_three_unique_conditions_with_indonesian_messages(): void
    {
        $payload = $this->payload();
        $payload['results'] = [
            $payload['results'][0],
            $payload['results'][0],
            $payload['results'][2],
        ];

        $this->actingAs($this->inspector())
            ->from(route('form-pengukurans.create'))
            ->post(route('form-pengukurans.store'), $payload)
            ->assertRedirect(route('form-pengukurans.create'))
            ->assertSessionHasErrors(['results.1.condition' => 'Setiap kondisi hasil harus unik.']);

        $this->assertDatabaseCount('form_pengukurans', 0);
    }

    public function test_invalid_status_and_numeric_measurement_have_indonesian_messages(): void
    {
        $payload = $this->payload();
        $payload['status'] = 'selesai';
        $payload['results'][0]['h'] = 'bukan angka';

        $this->actingAs($this->inspector())
            ->post(route('form-pengukurans.store'), $payload)
            ->assertSessionHasErrors([
                'status' => 'Status yang dipilih tidak valid.',
                'results.0.h' => 'Nilai pengukuran harus berupa angka.',
            ]);
    }

    public function test_update_synchronizes_results_and_ignores_client_ids_for_upsert(): void
    {
        $form = FormPengukuran::factory()->create();
        foreach (FormPengukuranHasil::CONDITIONS as $condition) {
            FormPengukuranHasil::factory()->for($form)->forCondition($condition)->create();
        }

        $payload = $this->payload($form);
        foreach ($form->hasils as $index => $result) {
            $payload['results'][$index]['id'] = $result->id;
        }
        $payload['results'][0]['h'] = 88.125;

        $this->actingAs($this->inspector())
            ->put(route('form-pengukurans.update', $form), $payload)
            ->assertRedirect(route('form-pengukurans.show', $form));

        $this->assertCount(3, $form->refresh()->hasils);
        $this->assertDatabaseHas('form_pengukuran_hasils', [
            'form_pengukuran_id' => $form->id,
            'kondisi' => 'sebelum_cuci',
            'h' => 88.125,
        ]);
    }

    public function test_update_rejects_result_id_from_another_parent_without_changes(): void
    {
        $form = FormPengukuran::factory()->create(['status' => 'draft']);
        $otherForm = FormPengukuran::factory()->create();
        $foreignResult = FormPengukuranHasil::factory()->for($otherForm)->forCondition('sebelum_cuci')->create();
        $payload = $this->payload($form);
        $payload['status'] = 'disetujui';
        $payload['results'][0]['id'] = $foreignResult->id;

        $this->actingAs($this->inspector())
            ->put(route('form-pengukurans.update', $form), $payload)
            ->assertSessionHasErrors(['results' => 'Hasil pengukuran tidak terdaftar pada form ini.']);

        $this->assertSame('draft', $form->refresh()->status);
        $this->assertDatabaseHas('form_pengukuran_hasils', ['id' => $foreignResult->id, 'form_pengukuran_id' => $otherForm->id]);
    }

    public function test_delete_cascades_results(): void
    {
        $form = FormPengukuran::factory()->create();
        foreach (FormPengukuranHasil::CONDITIONS as $condition) {
            FormPengukuranHasil::factory()->for($form)->forCondition($condition)->create();
        }

        $this->actingAs($this->inspector())
            ->delete(route('form-pengukurans.destroy', $form))
            ->assertRedirect(route('form-pengukurans.index'));

        $this->assertDatabaseMissing('form_pengukurans', ['id' => $form->id]);
        $this->assertDatabaseMissing('form_pengukuran_hasils', ['form_pengukuran_id' => $form->id]);
    }

    public function test_show_returns_explicit_camel_case_props(): void
    {
        $form = FormPengukuran::factory()->create();
        foreach (FormPengukuranHasil::CONDITIONS as $condition) {
            FormPengukuranHasil::factory()->for($form)->forCondition($condition)->create();
        }

        $this->actingAs($this->inspector())
            ->get(route('form-pengukurans.show', $form))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('FormPengukurans/Show')
                ->where('formPengukuran.id', $form->id)
                ->where('formPengukuran.areaId', $form->area_id)
                ->has('formPengukuran.results', 3)
                ->missing('formPengukuran.area_id'));
    }

    private function inspector(): User
    {
        $inspector = User::factory()->create(['is_approved' => true]);
        $inspector->assignRole('inspector');

        return $inspector;
    }

    private function payload(?FormPengukuran $form = null): array
    {
        $area = $form?->area ?? Area::factory()->create();
        $masterMould = $form?->masterMould ?? MasterMould::factory()->create();
        $modelSize = $form?->modelSize ?? ModelSize::factory()->create();
        $customer = $form?->customer ?? Customer::factory()->create();

        return [
            'areaId' => $area->id,
            'masterMouldId' => $masterMould->id,
            'modelSizeId' => $modelSize->id,
            'customerId' => $customer->id,
            'hariKe' => '7',
            'mesin' => 'MC-01',
            'tanggalOutputSample' => '2026-10-14',
            'tanggalPengukuran' => '2026-10-15',
            'tanggalNaikMould' => '2026-10-13',
            'dibuatOleh' => 'Operator Satu',
            'diperiksaOleh' => 'Supervisor Satu',
            'status' => 'menunggu_pemeriksaan',
            'results' => [
                ['condition' => 'sebelum_cuci', 'remarks' => 'Awal', 'h' => 12.345, 'sl_titik' => 9.876],
                ['condition' => 'sesudah_cuci', 'remarks' => null, 'h' => 11.234, 'w' => 8.765],
                ['condition' => 'setelah_bakar', 'remarks' => 'Akhir', 'h' => null, 'beading_267' => 3.210],
            ],
        ];
    }
}
