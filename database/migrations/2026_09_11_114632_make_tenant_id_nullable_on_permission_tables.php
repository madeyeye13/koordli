<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE roles SET tenant_id = 0 WHERE tenant_id IS NULL");
        DB::statement("UPDATE model_has_roles SET tenant_id = 0 WHERE tenant_id IS NULL");
        DB::statement("UPDATE model_has_permissions SET tenant_id = 0 WHERE tenant_id IS NULL");
    }

    public function down(): void
    {
    }
};
