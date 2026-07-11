<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->string('status')->default('active')->after('industry');
            $table->timestamp('trial_ends_at')->nullable()->after('status');
            $table->string('billing_email')->nullable()->after('country');
            $table->string('vat_id')->nullable()->after('billing_email');
            $table->text('billing_address')->nullable()->after('vat_id');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['slug', 'status', 'trial_ends_at', 'billing_email', 'vat_id', 'billing_address']);
        });
    }
};
