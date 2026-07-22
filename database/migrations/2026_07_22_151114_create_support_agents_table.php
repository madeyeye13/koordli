<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_user_id')->unique()->constrained('platform_users')->cascadeOnDelete();
            $table->boolean('is_available')->default(false);
            $table->string('status')->default('offline'); // online|away|offline
            $table->unsignedInteger('max_concurrent_chats')->default(3);
            $table->unsignedInteger('active_chat_count')->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_agents');
    }
};