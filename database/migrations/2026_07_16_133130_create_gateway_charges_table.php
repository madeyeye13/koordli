<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_charges', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');          // paystack|flutterwave
            $table->string('region');           // local|international
            $table->decimal('percentage', 5, 4)->default(0); // e.g. 0.015 = 1.5%
            $table->decimal('fixed_fee', 10, 2)->default(0); // e.g. 100.00 NGN
            $table->decimal('cap', 10, 2)->nullable();        // e.g. 2000.00 NGN
            $table->boolean('absorb')->default(true);         // platform absorbs charge
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_charges');
    }
};