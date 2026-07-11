<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'analyses',
            'bundle_tool',
            'bundles',
            'company_tool',
            'company_user',
            'gmbh_inputs',
            'immobilien_inputs',
            'jahresabschluss_inputs',
            'kpi_definitions',
            'kpi_monthly_targets',
            'kpi_results',
            'kpi_thresholds',
            'kpi_user_assignments',
            'kpi_values',
            'leads',
            'model_has_permissions',
            'model_has_roles',
            'password_resets',
            'paypal_transactions',
            'personal_access_tokens',
            'plan_bundle',
            'plan_tool',
            'plans',
            'role_has_permissions',
            'roles',
            'settings',
            'subscription_items',
            'subscriptions',
            'tools',
            'companies',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Legacy cleanup migration; reverse is intentionally empty.
    }
};
