<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_subtopics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_project_id')->constrained('keyword_projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('long_tail_keyword')->nullable();
            $table->unsignedInteger('search_volume')->nullable();
            $table->decimal('cpc', 10, 2)->nullable();
            $table->string('competition', 16)->nullable();
            $table->unsignedTinyInteger('competition_index')->nullable();
            $table->decimal('low_bid', 10, 2)->nullable();
            $table->decimal('high_bid', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('cluster_title')->nullable();
            $table->longText('cluster_content')->nullable();
            $table->string('cluster_meta_description', 320)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_subtopics');
    }
};
