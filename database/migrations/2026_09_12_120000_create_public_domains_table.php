<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rsvp_form_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('domain')->unique();
            $table->string('domain_type', 20); // subdomain|apex
            $table->string('status', 20)->default('pending'); // pending|verified|failed|disabled
            $table->string('verification_token');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->string('observed_dns_value')->nullable();
            $table->string('expected_dns_value')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->string('notification_fingerprint')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'domain_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_domains');
    }
};
