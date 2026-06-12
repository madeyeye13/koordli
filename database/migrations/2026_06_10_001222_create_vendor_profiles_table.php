<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_application_id')->nullable()->constrained('vendor_applications')->nullOnDelete();
            $table->foreignId('vendor_category_id')->nullable()->constrained('vendor_categories')->nullOnDelete();
            $table->string('business_name');
            $table->text('description')->nullable();
            $table->json('social_links')->nullable();
            $table->json('portfolio_urls')->nullable();
            $table->string('rate_card_path')->nullable(); // uploaded rate card file path
            $table->json('pricing_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};