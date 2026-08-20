<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'plans', 'plan_features', 'platform_users', 'roles',
        'rsvp_questions', 'rsvp_responses', 'rsvp_response_answers',
        'runsheets', 'runsheet_items', 'tasks', 'tenants',
        'tenant_event_statuses', 'tenant_feature_overrides', 'tenant_labels',
        'tenant_task_categories', 'users', 'vendors', 'vendor_accounts',
        'vendor_applications', 'vendor_categories', 'vendor_event_assignments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY id BIGINT UNSIGNED AUTO_INCREMENT");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY id BIGINT UNSIGNED");
        }
    }
};