<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'view analyses',
            'create analyses',
            'edit analyses',
            'delete analyses',
            'export pdf',
            'manage companies',
            'manage users',
            'manage kpi thresholds',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewer->syncPermissions(['view analyses', 'export pdf']);

        $analyst = Role::firstOrCreate(['name' => 'Analyst']);
        $analyst->syncPermissions([
            'view analyses', 'create analyses', 'edit analyses', 'export pdf', 'manage companies',
        ]);

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());
    }
}
