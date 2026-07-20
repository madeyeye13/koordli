<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_prices', function (Blueprint $table) {
            $table->string('billing_cycle')->default('monthly')->after('amount'); // monthly|annual
            $table->decimal('amount_with_charges', 10, 2)->nullable()->after('billing_cycle');
            $table->decimal('annual_discount_percent', 5, 2)->default(0)->after('amount_with_charges');
        });
    }

    public function down(): void
    {
        Schema::table('plan_prices', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'amount_with_charges', 'annual_discount_percent']);
        });
    }
};