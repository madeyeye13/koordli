<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // Simple string label, not a DB enum — easy to add/reorder
            // phases later without a migration. A small fixed PHP-side
            // list drives the UI dropdown (see ChecklistPhase below).
            $table->string('phase');

            // Purely a sort/reference aid alongside phase — never used
            // for real date-math, per the "don't over-engineer" instruction.
            $table->unsignedInteger('days_before_event')->nullable();

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            // NULL = not yet converted, purely informational. Set = this
            // item now has a real, trackable Task — the checklist item
            // becomes a thin pointer, never a second tracking mechanism.
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'checklist_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('checklist_items'); }
};