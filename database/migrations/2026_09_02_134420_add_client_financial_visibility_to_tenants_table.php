<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->json('client_financial_visibility')->nullable()->after('client_notification_settings');
        });
    }
    public function down(): void
    {
        Schema::table('tenants', fn (Blueprint $t) => $t->dropColumn('client_financial_visibility'));
    }
};