<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moodboard_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('moodboard_id')->constrained('moodboards')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('moodboard_sections')->nullOnDelete();

            $table->enum('type', ['image', 'text', 'color', 'link', 'file']);

            // Desktop free-form canvas coordinates.
            $table->integer('pos_x')->default(0);
            $table->integer('pos_y')->default(0);
            $table->integer('width')->default(220);
            $table->integer('height')->default(220);

            // Mobile's simplified reorderable-list rendering reads THIS,
            // never pos_x/pos_y — same row, two independent rendering
            // interpretations of one canonical dataset, per the confirmed
            // design decision.
            $table->unsignedInteger('sort_order')->default(0);

            // Type-specific fields live here rather than as nullable
            // columns per type — matches Document's own type-column
            // pattern (file/logo/link on one table) and keeps adding a
            // 6th/7th item type to an enum-value + JSON-key change,
            // never a new migration for new columns.
            // image: {caption, note, source_url}
            // text:  {heading, content}
            // color: {value, name, note}
            // link:  {url, title, description}  ← manually entered, NOT
            //         scraped — no OpenGraph/metadata-fetching mechanism
            //         exists anywhere in this app yet (confirmed absent
            //         via codebase search); building real URL scraping
            //         was explicitly out of scope for this pass.
            // file:  {name, description}
            $table->json('data')->nullable();

            // Only populated for image/file types — points at the REAL
            // Document row so storage quota, deletion, and the existing
            // DocumentStorageService counting all flow through the exact
            // same pipeline as Media Library. Never a parallel upload path.
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();

            // The light, optional, one-directional reference — a pointer
            // only, never automated/bidirectional coupling per spec.
            $table->string('linked_type')->nullable(); // 'vendor' | 'vendor_assignment' | 'task'
            $table->unsignedBigInteger('linked_id')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'moodboard_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodboard_items');
    }
};