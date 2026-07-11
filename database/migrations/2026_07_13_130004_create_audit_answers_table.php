<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('audits')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('audit_questions')->cascadeOnDelete();
            $table->integer('score')->nullable();
            $table->text('comment')->nullable();
            $table->string('evidence_file_path')->nullable();
            $table->json('selected_options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_answers');
    }
};
