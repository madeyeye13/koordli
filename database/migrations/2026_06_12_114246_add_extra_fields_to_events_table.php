<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('name');
            $table->string('client_phone')->nullable()->after('client_name');
            $table->string('client_email')->nullable()->after('client_phone');
            $table->time('start_time')->nullable()->after('date');
            $table->date('end_date')->nullable()->after('start_time');
            $table->time('end_time')->nullable()->after('end_date');
            $table->string('location')->nullable()->after('venue'); // city/state
            $table->decimal('agreed_budget', 15, 2)->nullable()->after('max_guests');
            $table->text('notes')->nullable()->after('agreed_budget');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'client_name', 'client_phone', 'client_email',
                'start_time', 'end_date', 'end_time',
                'location', 'agreed_budget', 'notes',
            ]);
        });
    }
};