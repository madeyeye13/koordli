<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('client_vendor_involvement_level', [
                'none', 'view_only', 'approve_selections', 'full_participation',
            ])->default('none')->after('branding');

            $table->text('vendor_disclaimer_text')->nullable()->after('client_vendor_involvement_level');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['client_vendor_involvement_level', 'vendor_disclaimer_text']);
        });
    }
};