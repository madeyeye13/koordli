<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('assigned_to')
                  ->constrained('users')
                  ->nullOnDelete();

            // Make event_id explicitly nullable to support company tasks
            $table->foreignId('event_id')
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};