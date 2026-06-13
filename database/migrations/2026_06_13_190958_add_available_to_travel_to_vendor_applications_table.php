<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_applications', function (Blueprint $table) {
            $table->boolean('available_to_travel')->default(false)->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_applications', function (Blueprint $table) {
            $table->dropColumn('available_to_travel');
        });
    }
};