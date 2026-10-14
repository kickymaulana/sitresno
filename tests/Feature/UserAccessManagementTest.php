<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_view_user_access_page(): void
    {
        $admin = User::query()->where('nik', 'D260065')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('users.access.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Index')
                ->has('users')
                ->where('roles', ['admin', 'inspector', 'supervisor', 'manager']));
    }

    public function test_non_admin_cannot_view_user_access_page(): void
    {
        $inspector = User::factory()->create();
        $inspector->assignRole('inspector');

        $this->actingAs($inspector)
            ->get(route('users.access.index'))
            ->assertForbidden();
    }

    public function test_admin_can_approve_user_and_assign_role(): void
    {
        $admin = User::query()->where('nik', 'D260065')->firstOrFail();
        $user = User::factory()->create([
            'is_approved' => false,
            'approved_at' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('users.access.update', $user), [
                'role' => 'supervisor',
                'is_approved' => true,
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_approved);
        $this->assertNotNull($user->approved_at);
        $this->assertTrue($user->hasRole('supervisor'));
    }

    public function test_invalid_role_is_rejected(): void
    {
        $admin = User::query()->where('nik', 'D260065')->firstOrFail();
        $user = User::factory()->create(['is_approved' => false]);

        $this->actingAs($admin)
            ->patch(route('users.access.update', $user), [
                'role' => 'owner',
                'is_approved' => true,
            ])
            ->assertSessionHasErrors('role');

        $this->assertFalse($user->refresh()->is_approved);
        $this->assertCount(0, $user->roles);
    }

    public function test_non_admin_cannot_update_user_access(): void
    {
        $inspector = User::factory()->create();
        $inspector->assignRole('inspector');
        $user = User::factory()->create(['is_approved' => false]);

        $this->actingAs($inspector)
            ->patch(route('users.access.update', $user), [
                'role' => 'manager',
                'is_approved' => true,
            ])
            ->assertForbidden();

        $this->assertFalse($user->refresh()->is_approved);
    }
}
