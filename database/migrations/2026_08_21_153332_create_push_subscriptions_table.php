<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('endpoint'); // push endpoint URLs can be long — kept unindexed on purpose
            $table->string('endpoint_hash', 64); // sha256 of endpoint — used for the actual unique constraint, since MySQL can't safely index a long TEXT column directly
            $table->string('p256dh_key');
            $table->string('auth_token');
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['notifiable_type', 'notifiable_id', 'endpoint_hash'], 'unique_subscription_per_device');
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};