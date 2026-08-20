<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('sender_type'); // tenant_user|client|vendor_account|system
            $table->unsignedBigInteger('sender_id')->nullable(); // null for system messages
            $table->text('body'); // plain text, linkified at render — same pattern as Support System messages
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable(); // soft-delete: message shows "This message was deleted"
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};