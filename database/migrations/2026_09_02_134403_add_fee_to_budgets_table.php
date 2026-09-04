<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->string('fee_type')->nullable()->after('client_paid');
            $table->decimal('fee_amount', 12, 2)->nullable()->after('fee_type');
            // Optional context stored alongside the confirmed number —
            // e.g. "15% of budget" or "₦50,000 × 8 guests" — purely
            // descriptive, never re-evaluated, never drives a calculation.
            $table->string('fee_note')->nullable()->after('fee_amount');
        });
    }
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $t) {
            $t->dropColumn(['fee_type', 'fee_amount', 'fee_note']);
        });
    }
};