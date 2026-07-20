<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('vendor_contract_id')->nullable()->constrained('vendor_contracts')->nullOnDelete();
            $table->foreignId('vendor_event_assignment_id')->nullable()->constrained('vendor_event_assignments')->nullOnDelete();

            $table->string('invoice_number');
            $table->string('title')->nullable(); // e.g. "Deposit Invoice", "Final Balance"
            $table->date('issue_date');
            $table->date('due_date')->nullable();

            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            // total_amount = amount + tax_amount - discount_amount (computed, but stored for query speed)
            $table->decimal('total_amount', 12, 2);

            $table->string('status')->default('draft'); // draft|sent|partially_paid|paid|overdue|cancelled
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index(['event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_invoices');
    }
};