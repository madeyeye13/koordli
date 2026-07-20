<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('annual_discount_percent', 5, 2)->default(0)->after('trial_days');
            $table->json('allowed_cycles')->nullable()->after('annual_discount_percent'); // ["monthly","annual"]
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['annual_discount_percent', 'allowed_cycles']);
        });
    }
};