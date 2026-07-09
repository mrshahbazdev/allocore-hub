<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_definitions', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('source')->default('manual')->after('category'); // manual | audit | invoice | ...
            $table->string('source_key')->nullable()->after('source');      // e.g. audit.umsatz
            $table->boolean('is_connected')->default(false)->after('source_key');
            $table->decimal('scale_max', 20, 4)->nullable()->after('is_connected'); // e.g. 5 for audit scores

            $table->unique(['company_id', 'source', 'source_key']);
        });

        Schema::table('kpi_values', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('status');
            $table->string('external_ref')->nullable()->after('source'); // idempotency key from spoke tool
            $table->index(['kpi_definition_id', 'external_ref']);
        });
    }

    public function down(): void
    {
        Schema::table('kpi_definitions', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'source', 'source_key']);
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'source', 'source_key', 'is_connected', 'scale_max']);
        });

        Schema::table('kpi_values', function (Blueprint $table) {
            $table->dropIndex(['kpi_definition_id', 'external_ref']);
            $table->dropColumn(['source', 'external_ref']);
        });
    }
};
