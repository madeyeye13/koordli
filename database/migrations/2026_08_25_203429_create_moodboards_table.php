<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moodboards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            // Nullable on purpose: a TEMPLATE moodboard has no real event
            // yet — it exists purely to be duplicated onto a future event.
            $table->foreignId('event_id')->nullable()->constrained('events')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'in_review', 'approved', 'archived'])->default('draft');

            $table->foreignId('cover_document_id')->nullable()
                ->constrained('documents')->nullOnDelete();

            $table->boolean('is_client_visible')->default(false);

            // Template mechanism
            $table->boolean('is_template')->default(false);
            $table->foreignId('template_source_id')->nullable()
                ->constrained('moodboards')->nullOnDelete();
            $table->foreignId('event_type_id')->nullable()
                ->constrained('event_types')->nullOnDelete(); // only meaningful when is_template = true

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'event_id']);
            $table->index(['tenant_id', 'is_template', 'event_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodboards');
    }
};