<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. KPI Thresholds
        $this->call(KpiThresholdsSeeder::class);

        // 3. Tools, Plans & Bundles
        $this->call(ToolSeeder::class);
        $this->call(PlanSeeder::class);

        // 4. Demo Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@allocore.de'],
            User::factory()->raw(['name' => 'Admin User', 'email' => 'admin@allocore.de'])
        );
        $admin->assignRole('Admin');

        // 4. Demo Analyst User
        $analyst = User::firstOrCreate(
            ['email' => 'analyst@allocore.de'],
            User::factory()->raw(['name' => 'Analyst User', 'email' => 'analyst@allocore.de'])
        );
        $analyst->assignRole('Analyst');

        // 5. Invoice Module Demo Data
        $this->call(InvoiceModuleSeeder::class);
    }
}
