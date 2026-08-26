<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // NULL = inherit the tenant's default setting (the common case,
            // zero extra clicks per event). A real value = this event
            // explicitly overrides the tenant default.
            $table->enum('client_vendor_involvement_override', [
                'none', 'view_only', 'approve_selections', 'full_participation',
            ])->nullable()->after('rsvp_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('client_vendor_involvement_override');
        });
    }
};