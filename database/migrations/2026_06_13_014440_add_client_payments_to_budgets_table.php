<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('client_paid', 12, 2)->default(0)->after('total_amount');
        });

        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->date('paid_on');
            $table->string('payment_method')->default('transfer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('client_paid');
        });
        Schema::dropIfExists('client_payments');
    }
};