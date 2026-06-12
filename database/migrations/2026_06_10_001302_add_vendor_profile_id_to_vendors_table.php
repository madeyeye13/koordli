<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('vendor_profile_id')
                  ->nullable()
                  ->after('vendor_category_id')
                  ->constrained('vendor_profiles')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['vendor_profile_id']);
            $table->dropColumn('vendor_profile_id');
        });
    }
};