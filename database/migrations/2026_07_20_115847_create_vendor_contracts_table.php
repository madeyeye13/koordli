<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('vendor_event_assignment_id')->nullable()->constrained('vendor_event_assignments')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('vendor_contract_templates')->nullOnDelete();

            $table->string('title');
            $table->longText('content'); // editable HTML, populated from template
            $table->decimal('contract_amount', 12, 2)->nullable();
            $table->string('payment_schedule')->nullable();

            $table->string('status')->default('draft'); // draft|sent|signed|expired|cancelled

            $table->string('unsigned_file_path')->nullable(); // exported PDF of the drafted contract
            $table->string('signed_file_path')->nullable();   // uploaded signed copy

            $table->date('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_contracts');
    }
};