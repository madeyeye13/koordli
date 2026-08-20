<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->boolean('reminders_enabled')->default(true);
            $table->boolean('business_hours_only')->default(false);
            $table->time('business_hours_start')->nullable();
            $table->time('business_hours_end')->nullable();
            $table->boolean('weekend_reminders')->default(true);
            $table->time('dnd_start')->nullable();
            $table->time('dnd_end')->nullable();
            $table->unsignedInteger('default_escalation_hours')->default(24);
            $table->string('digest_mode')->default('off'); // off|daily|weekly
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_notification_settings');
    }
};