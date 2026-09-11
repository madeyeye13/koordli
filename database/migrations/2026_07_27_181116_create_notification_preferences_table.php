<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The real, current schema — see NotificationPreference::$fillable.
     * An earlier migration (2026_06_09_131107) creates this table first
     * with an old, superseded schema (user_id/channel/event_type). On a
     * fresh database that earlier migration runs first, so this one
     * must replace it with the correct, current structure rather than
     * skip when the table already exists.
     */
    public function up(): void
    {
        Schema::dropIfExists('notification_preferences');

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('notifiable_type')->default(\App\Models\Tenant\User::class);
            $table->unsignedBigInteger('notifiable_id');
            $table->string('category');
            $table->json('channels');
            $table->timestamps();

            $table->unique(['notifiable_type', 'notifiable_id', 'category'], 'unique_pref_per_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
