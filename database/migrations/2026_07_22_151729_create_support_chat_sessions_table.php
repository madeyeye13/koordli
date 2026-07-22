<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->unique()->constrained('support_tickets')->cascadeOnDelete();
            $table->string('status')->default('bot'); // bot|waiting|active|ended
            $table->timestamp('bot_engaged_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('agent_joined_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('ended_by')->nullable(); // tenant|agent|system
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_chat_sessions');
    }
};