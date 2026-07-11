<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('kpi_values');
        Schema::dropIfExists('kpi_definitions');
        Schema::dropIfExists('kpi_monthly_targets');
        Schema::dropIfExists('kpi_user_assignments');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Legacy cleanup migration; reverse is intentionally empty.
    }
};
