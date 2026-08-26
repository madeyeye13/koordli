<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->foreignId('shared_moodboard_id')->nullable()->after('reply_to_message_id')
                ->constrained('moodboards')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropForeign(['shared_moodboard_id']);
            $table->dropColumn('shared_moodboard_id');
        });
    }
};