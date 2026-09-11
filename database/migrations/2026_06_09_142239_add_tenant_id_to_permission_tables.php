<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Made idempotent: config/permission.php sets team_foreign_key to
     * 'tenant_id', meaning Spatie's own create_permission_tables
     * migration already creates this column via its teams feature.
     * This migration predates that config and is now redundant on a
     * fresh database — but is kept (rather than deleted) in case any
     * existing environment's migration history depends on it having
     * run. Each column addition is guarded so it's a safe no-op
     * wherever the column already exists.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'roles_tenant_id_index');
            });
        }

        if (!Schema::hasColumn('model_has_roles', 'tenant_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('role_id');
                $table->index('tenant_id', 'model_has_roles_tenant_id_index');
            });
        }

        if (!Schema::hasColumn('model_has_permissions', 'tenant_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('permission_id');
                $table->index('tenant_id', 'model_has_permissions_tenant_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex('roles_tenant_id_index');
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('model_has_roles', 'tenant_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropIndex('model_has_roles_tenant_id_index');
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('model_has_permissions', 'tenant_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropIndex('model_has_permissions_tenant_id_index');
                $table->dropColumn('tenant_id');
            });
        }
    }
};
