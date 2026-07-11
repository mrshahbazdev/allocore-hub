<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_subtopic_id')->constrained('keyword_subtopics')->cascadeOnDelete();
            $table->text('question');
            $table->longText('answer')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_questions');
    }
};
