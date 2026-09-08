// create_event_story_chapters_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_story_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->unsignedInteger('sort_order')->default(0);
            // Who wrote/last edited this chapter — either side can edit
            // any chapter; this just tracks provenance, not ownership.
            $table->enum('last_edited_by_type', ['tenant', 'client'])->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('event_story_chapters'); }
};