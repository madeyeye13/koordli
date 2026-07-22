<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('from_agent_id')->nullable()->constrained('support_agents')->nullOnDelete();
            $table->foreignId('to_agent_id')->constrained('support_agents')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('platform_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_assignment_history');
    }
};