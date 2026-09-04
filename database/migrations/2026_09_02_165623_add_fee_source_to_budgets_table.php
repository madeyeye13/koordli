<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->string('fee_source')->nullable()->after('fee_note');
        });
    }
    public function down(): void
    {
        Schema::table('budgets', fn (Blueprint $t) => $t->dropColumn('fee_source'));
    }
};