<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('tenant_last_read_at')->nullable()->after('rated_at');
            $table->timestamp('agent_last_read_at')->nullable()->after('tenant_last_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['tenant_last_read_at', 'agent_last_read_at']);
        });
    }
};