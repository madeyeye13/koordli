<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_access_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            // Polymorphic — either App\Models\Tenant\User (staff) or
            // App\Models\Central\VendorAccount (vendor), matching the
            // same actor_type/actor_id pattern already used in
            // ActivityTimeline and elsewhere throughout this app.
            $table->string('person_type');
            $table->unsignedBigInteger('person_id');

            $table->string('token', 24)->unique();

            // Per-person, self-set — never a tenant-wide shared value.
            $table->string('pin_hash')->nullable();
            $table->boolean('pin_enabled')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            $table->index(['person_type', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_access_links');
    }
};