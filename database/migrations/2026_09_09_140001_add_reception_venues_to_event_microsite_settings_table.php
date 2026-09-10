<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_microsite_settings', function (Blueprint $table) {
            $table->boolean('separate_venues_enabled')->default(false)->after('gate_venue_address');
            $table->string('reception_venue')->nullable()->after('separate_venues_enabled');
            $table->text('reception_address')->nullable()->after('reception_venue');
            $table->time('reception_time')->nullable()->after('reception_address');
        });
    }

    public function down(): void
    {
        Schema::table('event_microsite_settings', function (Blueprint $table) {
            $table->dropColumn(['separate_venues_enabled', 'reception_venue', 'reception_address', 'reception_time']);
        });
    }
};
