<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('rsvp_responses');
        Schema::dropIfExists('guests');
        Schema::enableForeignKeyConstraints();

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->nullable(); // e.g. Family, Friends, Colleagues, VIP
            $table->text('notes')->nullable();
            $table->string('rsvp_status')->default('pending'); // pending|confirmed|declined
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event_id']);
        });

        Schema::create('rsvp_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('rsvp_form_id')->constrained('rsvp_forms')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('respondent_name');
            $table->string('respondent_email')->nullable();
            $table->string('respondent_phone')->nullable();
            $table->string('status')->default('pending'); // pending|confirmed|declined
            $table->unsignedInteger('plus_one_count')->default(0);
            $table->string('qr_token')->unique()->nullable();
            $table->string('edit_token')->unique()->nullable(); // secure edit link
            $table->json('response_data')->nullable(); // custom question answers
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event_id']);
            $table->index('qr_token');
            $table->index('edit_token');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('rsvp_responses');
        Schema::dropIfExists('guests');
        Schema::enableForeignKeyConstraints();
    }
};