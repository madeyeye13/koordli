<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rsvp_forms', function (Blueprint $table) {
            $table->json('milestones_notified')->nullable()->after('guest_limit');
        });
    }

    public function down(): void
    {
        Schema::table('rsvp_forms', function (Blueprint $table) {
            $table->dropColumn('milestones_notified');
        });
    }
};