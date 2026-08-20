<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->morphs('subject'); // e.g. Task, Contract, Invoice — this already creates the needed index
            $table->string('event_type'); // e.g. 'assigned', 'reminder_sent', 'completed'
            $table->text('description');
            $table->string('actor_type')->nullable(); // tenant_user|vendor|system|bot
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_timelines');
    }
};