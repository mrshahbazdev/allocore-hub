<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name_de');
            $table->string('name_en');
            $table->text('description_de')->nullable();
            $table->text('description_en')->nullable();
            $table->string('formula')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('target_value', 20, 4)->nullable();
            $table->decimal('warning_threshold', 20, 4)->nullable();
            $table->decimal('critical_threshold', 20, 4)->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('direction', ['higher_better', 'lower_better'])->default('higher_better');
            $table->string('category')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kpi_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 20, 4);
            $table->date('recorded_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['on_target', 'warning', 'critical'])->default('on_target');
            $table->timestamps();

            $table->index(['kpi_definition_id', 'recorded_at']);
        });

        Schema::create('kpi_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_value', 20, 4);
            $table->decimal('growth_rate', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['kpi_definition_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_monthly_targets');
        Schema::dropIfExists('kpi_values');
        Schema::dropIfExists('kpi_definitions');
    }
};
