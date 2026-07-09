<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('tool');            // audit | invoice | ...
            $table->string('name');            // human label
            $table->string('base_url')->nullable();
            $table->string('api_key')->unique(); // key the spoke tool sends in X-Allocore-Api-Key
            $table->boolean('enabled')->default(true);
            $table->string('status')->default('pending'); // pending | connected | error
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'tool']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_accesses');
    }
};
