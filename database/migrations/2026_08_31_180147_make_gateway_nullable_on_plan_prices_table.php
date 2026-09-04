<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_prices', function (Blueprint $table) {
            $table->string('gateway')->nullable()->default(null)->change();
            $table->string('gateway_plan_id')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('plan_prices', function (Blueprint $table) {
            $table->string('gateway')->nullable(false)->change();
            $table->string('gateway_plan_id')->nullable(false)->change();
        });
    }
};