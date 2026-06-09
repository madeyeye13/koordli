<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_settings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // NGN, GHS, GBP, USD
            $table->string('name');
            $table->string('symbol', 10);
            $table->boolean('is_active')->default(true);
            $table->json('gateway_supported')->nullable(); // ["paystack","flutterwave"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_settings');
    }
};