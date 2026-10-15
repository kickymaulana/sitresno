<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $recordPermissions = [
            'records.view',
            'records.create',
            'records.update',
            'records.submit',
            'records.approve',
            'records.finalize',
        ];
        $masterPermissions = [
            'masters.view',
            'masters.create',
            'masters.update',
            'masters.delete',
        ];
        $permissions = [
            'users.view',
            'users.approve',
            'users.assign-role',
            ...$recordPermissions,
            ...$masterPermissions,
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('inspector', 'web')->syncPermissions([
            'records.view',
            'records.create',
            'records.update',
            'records.submit',
        ]);
        Role::findOrCreate('supervisor', 'web')->syncPermissions([
            'records.view',
            'records.approve',
        ]);
        Role::findOrCreate('manager', 'web')->syncPermissions([
            'records.view',
            'records.finalize',
        ]);

        $admin = User::query()->updateOrCreate(
            ['nik' => 'D260065'],
            [
                'name' => 'Kicky Maulana',
                'email' => 'd260065@sso.local',
                'password' => null,
                'email_verified_at' => now(),
                'is_approved' => true,
                'approved_at' => now(),
            ],
        );

        $admin->syncRoles(['admin']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
