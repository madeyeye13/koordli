<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('feature_flags')->updateOrInsert(
            ['key' => 'custom_subdomain'],
            [
                'label'       => 'Custom Subdomain',
                'description' => 'Allows tenant to use a custom subdomain (e.g. yourcompany.koordli.com) instead of the default.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('feature_flags')->where('key', 'custom_subdomain')->delete();
    }
};