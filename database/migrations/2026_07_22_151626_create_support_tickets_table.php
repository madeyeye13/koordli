<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('created_by_user_id'); // tenant User id (not FK — cross-db-safe pattern used elsewhere)
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium'); // low|medium|high|urgent
            $table->string('category')->nullable();
            $table->string('status')->default('open'); // open|in_progress|resolved|closed
            $table->string('source')->default('ticket'); // ticket|chat|email
            $table->foreignId('assigned_agent_id')->nullable()->constrained('support_agents')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('rating_comment')->nullable();
            $table->timestamp('rated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('assigned_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};