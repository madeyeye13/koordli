<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vendor_category_id')->nullable()->constrained('vendor_categories')->nullOnDelete();
            $table->string('business_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->json('social_links')->nullable();
            $table->text('service_description')->nullable();
            $table->string('rate_card_path')->nullable(); // uploaded rate card file path
            $table->json('portfolio_urls')->nullable();
            $table->json('pricing_info')->nullable();
            $table->string('status')->default('pending'); // pending|approved|rejected|suspended
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_applications');
    }
};