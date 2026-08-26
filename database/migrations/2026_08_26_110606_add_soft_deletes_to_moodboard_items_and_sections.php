<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moodboard_items', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('moodboard_sections', fn (Blueprint $t) => $t->softDeletes());
    }

    public function down(): void
    {
        Schema::table('moodboard_items', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('moodboard_sections', fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};