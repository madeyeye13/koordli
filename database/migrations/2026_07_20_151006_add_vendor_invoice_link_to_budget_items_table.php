<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->foreignId('vendor_invoice_id')->nullable()->after('budget_id')->constrained('vendor_invoices')->nullOnDelete();
            $table->string('source')->default('manual')->after('vendor_invoice_id'); // manual|vendor_invoice
        });
    }

    public function down(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_invoice_id');
            $table->dropColumn('source');
        });
    }
};