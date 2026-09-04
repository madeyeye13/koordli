<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            // Nullable, no default forced — every existing row stays
            // 'planner' in calculation logic (see Budget model changes
            // below), reproducing today's exact math unchanged.
            $table->string('responsible_party')->nullable()->after('paid');
        });
    }
    public function down(): void
    {
        Schema::table('budget_items', fn (Blueprint $t) => $t->dropColumn('responsible_party'));
    }
};