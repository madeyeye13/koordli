<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks to allow dropping
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('vendor_profiles');
        Schema::dropIfExists('vendor_applications');
        Schema::dropIfExists('vendors');

        Schema::enableForeignKeyConstraints();

        // New company-level vendors directory
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vendor_category_id')->nullable()->constrained('vendor_categories')->nullOnDelete();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Event vendor assignments
        Schema::create('vendor_event_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->decimal('amount_agreed', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vendor_event_assignments');
        Schema::dropIfExists('vendors');
        Schema::enableForeignKeyConstraints();
    }
};