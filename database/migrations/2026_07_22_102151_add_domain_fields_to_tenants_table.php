<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('subdomain')->nullable()->unique()->after('slug');
            $table->string('custom_domain')->nullable()->unique()->after('subdomain');
            $table->string('domain_verification_token')->nullable()->after('custom_domain');
            $table->timestamp('domain_verified_at')->nullable()->after('domain_verification_token');
            $table->timestamp('domain_last_checked_at')->nullable()->after('domain_verified_at');
            $table->string('domain_status')->default('unverified')->after('domain_last_checked_at'); // unverified|pending|verified|failed
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'subdomain', 'custom_domain', 'domain_verification_token',
                'domain_verified_at', 'domain_last_checked_at', 'domain_status',
            ]);
        });
    }
};