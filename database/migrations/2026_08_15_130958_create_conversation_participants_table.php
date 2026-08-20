<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('participant_type'); // tenant_user|client|vendor_account
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('added_by')->nullable(); // tenant User id who added them
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable(); // removed from conversation, not deleted (history preserved)
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'unique_participant_per_conversation');
            $table->index(['participant_type', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};