<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('platform_users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('invite_token')->nullable()->unique()->after('is_active');
            $table->timestamp('invited_at')->nullable()->after('invite_token');
            $table->timestamp('invite_accepted_at')->nullable()->after('invited_at');
            $table->foreignId('invited_by')->nullable()->constrained('platform_users')->nullOnDelete()->after('invite_accepted_at');
        });
    }
    public function down(): void {
        Schema::table('platform_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by');
            $table->dropColumn(['is_active', 'invite_token', 'invited_at', 'invite_accepted_at']);
        });
    }
};