<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('current_period_end');
            $table->timestamp('grace_until')->nullable()->after('expires_at');
            $table->string('billing_cycle')->default('monthly')->after('grace_until');
            $table->boolean('reminder_14_sent')->default(false)->after('billing_cycle');
            $table->boolean('reminder_3_sent')->default(false)->after('reminder_14_sent');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'grace_until', 'billing_cycle', 'reminder_14_sent', 'reminder_3_sent']);
        });
    }
};