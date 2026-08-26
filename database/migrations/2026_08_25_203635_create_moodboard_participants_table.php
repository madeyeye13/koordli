<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moodboard_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('moodboard_id')->constrained('moodboards')->cascadeOnDelete();

            // Short string literals ('client', 'vendor_account') — matching
            // ConversationParticipant's convention exactly, per explicit
            // instruction to reuse that specific pattern, NOT the full
            // class-name convention used elsewhere in this app (Document,
            // QuickAccessLink, VendorSuggestion). Only 'client' is ever
            // written by this session's code — 'vendor_account' is a
            // structurally-supported value for a future pass, never
            // populated now.
            $table->string('participant_type');
            $table->unsignedBigInteger('participant_id');

            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['moodboard_id', 'participant_type', 'participant_id'], 'moodboard_participants_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodboard_participants');
    }
};