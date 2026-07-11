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
        Schema::create('audit_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('audit_templates')->cascadeOnDelete();
            $table->foreignId('pillar_id')->nullable()->constrained('audit_pillars')->cascadeOnDelete();
            $table->string('level')->nullable();
            $table->text('question');
            $table->text('description')->nullable();
            $table->string('question_type')->default('scale_1_to_5');
            $table->decimal('weight', 4, 2)->default(1.0);
            $table->boolean('is_required')->default(true);
            $table->text('failure_recommendation')->nullable();
            $table->json('options')->nullable();
            $table->foreignId('depends_on_question_id')->nullable()->constrained('audit_questions')->nullOnDelete();
            $table->string('depends_on_answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_questions');
    }
};
