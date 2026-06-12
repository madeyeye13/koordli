<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_self_registered')->default(false)->after('is_active');
            $table->boolean('onboarding_completed')->default(false)->after('is_self_registered');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_completed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_self_registered',
                'onboarding_completed',
                'onboarding_completed_at',
            ]);
        });
    }
};