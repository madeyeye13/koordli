<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_message_deletions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('message_id')->constrained('conversation_messages')->cascadeOnDelete();
            $table->string('participant_type'); // tenant_user|client|vendor_account
            $table->unsignedBigInteger('participant_id');
            $table->timestamps();

            $table->unique(['message_id', 'participant_type', 'participant_id'], 'unique_deletion_per_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_message_deletions');
    }
};