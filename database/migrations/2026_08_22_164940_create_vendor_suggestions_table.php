<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            // Polymorphic, matching the uploaded_by_type/id pattern already
            // used in Documents — either a Tenant\User (staff) or
            // Central\Client suggested this.
            $table->string('suggested_by_type');
            $table->unsignedBigInteger('suggested_by_id');

            $table->string('name');
            $table->string('category_label')->nullable(); // free-text, client-facing
            $table->foreignId('vendor_category_id')->nullable()
                ->constrained('vendor_categories')->nullOnDelete(); // optional structured mapping, set on approval
            $table->string('portfolio_link')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');

            $table->string('decided_by_type')->nullable();
            $table->unsignedBigInteger('decided_by_id')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();

            // Populated only on approval — links the suggestion to what it became
            $table->foreignId('resulting_vendor_id')->nullable()
                ->constrained('vendors')->nullOnDelete();
            $table->foreignId('resulting_assignment_id')->nullable()
                ->constrained('vendor_event_assignments')->nullOnDelete();

            $table->timestamps();

            $table->index(['suggested_by_type', 'suggested_by_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_suggestions');
    }
};