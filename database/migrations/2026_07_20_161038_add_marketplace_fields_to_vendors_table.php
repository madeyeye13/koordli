<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('is_active');
            $table->string('slug')->nullable()->unique()->after('is_public');
            $table->string('city')->nullable()->after('slug');
            $table->string('country', 2)->nullable()->after('city'); // ISO2, e.g. NG, GH
            $table->json('portfolio_images')->nullable()->after('country'); // future: array of image paths
            $table->text('public_description')->nullable()->after('portfolio_images'); // separate from internal 'description'
            $table->unsignedInteger('marketplace_views')->default(0)->after('public_description');
            $table->unsignedInteger('marketplace_inquiries')->default(0)->after('marketplace_views');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'is_public', 'slug', 'city', 'country',
                'portfolio_images', 'public_description',
                'marketplace_views', 'marketplace_inquiries',
            ]);
        });
    }
};