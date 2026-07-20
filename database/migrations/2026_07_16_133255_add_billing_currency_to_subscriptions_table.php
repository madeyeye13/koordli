<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->decimal('amount_ngn', 10, 2)->nullable()->after('amount');
            $table->decimal('exchange_rate', 10, 6)->nullable()->after('amount_ngn');
            $table->string('billing_cycle')->default('monthly')->after('exchange_rate');
            $table->uuid('uuid')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropColumn(['amount_ngn', 'exchange_rate', 'billing_cycle', 'uuid']);
        });
    }
};