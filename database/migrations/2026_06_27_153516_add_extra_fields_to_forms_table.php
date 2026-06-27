<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('description')->nullable()->after('slug');
            $table->string('hero_image')->nullable()->after('description');
            $table->string('hero_image_path')->nullable()->after('hero_image');
            $table->string('endpoint_token')->unique()->nullable()->after('settings');
            $table->string('tenant_email')->nullable()->after('endpoint_token');
            $table->string('tenant_phone')->nullable()->after('tenant_email');
            $table->text('tenant_address')->nullable()->after('tenant_phone');
            $table->string('consultation_type')->nullable()->after('tenant_address'); // physical|virtual|both
            $table->string('location')->nullable()->after('consultation_type');
            $table->integer('duration_minutes')->default(60)->after('location');
            $table->boolean('whatsapp_enabled')->default(false)->after('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'hero_image', 'hero_image_path', 'endpoint_token',
                'tenant_email', 'tenant_phone', 'tenant_address',
                'consultation_type', 'location', 'duration_minutes', 'whatsapp_enabled',
            ]);
        });
    }
};