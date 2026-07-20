<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('vendor_event_assignment_id')->constrained('vendor_event_assignments')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            $table->string('reviewer_type'); // planner | client
            $table->unsignedBigInteger('reviewer_id')->nullable(); // users.id or clients.id depending on type

            $table->unsignedTinyInteger('professionalism');
            $table->unsignedTinyInteger('communication');
            $table->unsignedTinyInteger('punctuality');
            $table->unsignedTinyInteger('quality_of_service');
            $table->unsignedTinyInteger('reliability');
            $table->unsignedTinyInteger('overall_experience');

            $table->text('comment')->nullable();

            $table->boolean('is_public')->default(false); // future marketplace-ready
            $table->boolean('is_archived')->default(false);

            $table->timestamp('locked_at')->nullable(); // becomes non-editable after this

            $table->timestamps();

            $table->unique(['vendor_event_assignment_id', 'reviewer_type'], 'one_review_per_type_per_assignment');
            $table->index(['vendor_id', 'reviewer_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_reviews');
    }
};