<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->string('primary_color')->default('#3B82F6');
            $table->string('font_family')->default('sans');
            $table->string('logo_position')->default('left');
            $table->string('header_style')->default('simple');
            $table->text('footer_message')->nullable();
            $table->string('signature_path')->nullable();
            $table->text('payment_terms')->nullable();
            $table->boolean('show_tax')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->boolean('enable_qr')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
