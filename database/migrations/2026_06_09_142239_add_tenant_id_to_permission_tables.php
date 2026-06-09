<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add tenant_id to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')
                  ->nullable()
                  ->after('id');
            $table->index('tenant_id', 'roles_tenant_id_index');
        });

        // Add tenant_id to model_has_roles pivot
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')
                  ->nullable()
                  ->after('role_id');
            $table->index('tenant_id', 'model_has_roles_tenant_id_index');
        });

        // Add tenant_id to model_has_permissions pivot
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')
                  ->nullable()
                  ->after('permission_id');
            $table->index('tenant_id', 'model_has_permissions_tenant_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('roles_tenant_id_index');
            $table->dropColumn('tenant_id');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('model_has_roles_tenant_id_index');
            $table->dropColumn('tenant_id');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex('model_has_permissions_tenant_id_index');
            $table->dropColumn('tenant_id');
        });
    }
};