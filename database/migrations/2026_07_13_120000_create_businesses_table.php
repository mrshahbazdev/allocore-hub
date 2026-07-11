<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('timezone')->default('UTC');
            $table->string('tax_number')->nullable();
            $table->text('bank_details')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->text('payment_terms')->nullable();
            $table->string('invoice_number_prefix')->default('INV');
            $table->unsignedInteger('invoice_number_next')->default(1);
            $table->string('estimate_number_prefix')->default('EST');
            $table->unsignedInteger('estimate_number_next')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
