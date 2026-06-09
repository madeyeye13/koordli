<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('meta_description')->nullable();  // SEO
            $table->string('og_image')->nullable();         // SEO - open graph image path
            $table->timestamp('deadline')->nullable();
            $table->unsignedInteger('guest_limit')->nullable();
            $table->json('branding')->nullable();           // bg_color, font_pairing, cover_image, accent_color
            $table->json('questions')->nullable();          // custom RSVP question definitions
            $table->json('ticket_settings')->nullable();    // QR ticket config
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_forms');
    }
};