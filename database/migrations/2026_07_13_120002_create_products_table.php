<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('manage_stock')->default(false);
            $table->string('unit')->default('unit');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
