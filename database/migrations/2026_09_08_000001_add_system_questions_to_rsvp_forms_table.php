<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rsvp_forms', function (Blueprint $table) {
            $table->json('system_questions')->nullable()->after('questions');
        });
    }

    public function down(): void
    {
        Schema::table('rsvp_forms', function (Blueprint $table) {
            $table->dropColumn('system_questions');
        });
    }
};