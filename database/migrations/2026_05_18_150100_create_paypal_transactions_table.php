<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paypal_order_id')->unique();
            $table->string('payer_email')->nullable();
            $table->string('payer_name')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('EUR');
            $table->string('status')->default('pending');
            $table->string('description')->nullable();
            $table->json('paypal_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paypal_transactions');
    }
};
