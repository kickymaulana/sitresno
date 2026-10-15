<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\MasterMould;
use App\Models\User;
use Database\Seeders\AreaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, AreaSeeder::class]);
    }

    public function test_area_seeder_creates_expected_areas(): void
    {
        $this->assertDatabaseHas('areas', ['name' => 'Pabrik Dalu 1']);
        $this->assertDatabaseHas('areas', ['name' => 'Pabrik Dalu 2']);
    }

    public function test_guest_is_redirected_from_master_data(): void
    {
        $this->get(route('areas.index'))->assertRedirect(route('login'));
    }

    public function test_unapproved_user_is_redirected_from_master_data(): void
    {
        $user = User::factory()->create(['is_approved' => false]);

        $this->actingAs($user)->get(route('areas.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_master_data(): void
    {
        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('inspector');

        $this->actingAs($user)->get(route('areas.index'))->assertForbidden();
        $this->actingAs($user)->post(route('areas.store'), ['name' => 'Area Baru'])->assertForbidden();
    }

    public function test_admin_has_master_permissions_while_other_roles_do_not(): void
    {
        $admin = $this->admin();
        $inspector = User::factory()->create(['is_approved' => true]);
        $inspector->assignRole('inspector');

        foreach (['masters.view', 'masters.create', 'masters.update', 'masters.delete'] as $permission) {
            $this->assertTrue($admin->can($permission));
            $this->assertFalse($inspector->can($permission));
        }
        $this->assertTrue($inspector->can('records.view'));
        $this->assertTrue($inspector->can('records.create'));
        $this->assertTrue($inspector->can('records.update'));
    }

    public function test_admin_can_create_update_and_delete_area(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('areas.store'), ['name' => 'Finishing'])->assertRedirect(route('areas.index'));
        $area = Area::query()->where('name', 'Finishing')->firstOrFail();

        $this->actingAs($admin)->put(route('areas.update', $area), ['name' => 'Packing'])->assertRedirect(route('areas.index'));
        $this->assertDatabaseHas('areas', ['id' => $area->id, 'name' => 'Packing']);

        $this->actingAs($admin)->delete(route('areas.destroy', $area))->assertRedirect(route('areas.index'));
        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }

    public function test_admin_can_view_master_page_with_explicit_props(): void
    {
        $area = Area::factory()->create(['name' => 'Laboratorium']);

        $this->actingAs($this->admin())
            ->get(route('areas.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Areas/Index')
                ->has('areas', 3)
                ->where('areas.0.id', $area->id)
                ->where('areas.0.name', 'Laboratorium'));
    }

    public function test_master_mould_validation_and_persistence_use_indonesian_messages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('master-moulds.create'))
            ->post(route('master-moulds.store'), [])
            ->assertSessionHasErrors(['code' => 'Kode mould wajib diisi.']);

        $this->actingAs($admin)->post(route('master-moulds.store'), [
            'code' => 'MLD-001',
            'description' => 'Mould utama',
        ])->assertRedirect(route('master-moulds.index'));

        $masterMould = MasterMould::query()->where('code', 'MLD-001')->firstOrFail();
        $this->actingAs($admin)->put(route('master-moulds.update', $masterMould), [
            'code' => 'MLD-002',
            'description' => null,
        ])->assertRedirect(route('master-moulds.index'));

        $this->assertDatabaseHas('master_moulds', ['id' => $masterMould->id, 'code' => 'MLD-002', 'description' => null]);
    }

    private function admin(): User
    {
        return User::query()->where('nik', 'D260065')->firstOrFail();
    }
}
