<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('notifiable_type')->default(\App\Models\Tenant\User::class);
            $table->unsignedBigInteger('notifiable_id');
            $table->string('category');
            $table->json('channels'); // e.g. ["database","mail"]
            $table->timestamps();

            $table->unique(['notifiable_type', 'notifiable_id', 'category'], 'unique_pref_per_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};