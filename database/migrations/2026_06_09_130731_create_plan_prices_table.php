<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('currency', 10); // NGN, GHS, GBP, USD etc.
            $table->decimal('amount', 12, 2);
            $table->string('gateway'); // paystack|flutterwave
            $table->string('gateway_plan_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['plan_id', 'currency', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};