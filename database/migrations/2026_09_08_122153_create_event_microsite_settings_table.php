// create_event_microsite_settings_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_microsite_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Per-section toggles — every section is opt-in, nothing forced
            $table->boolean('story_enabled')->default(false);
            $table->boolean('gallery_enabled')->default(false);
            $table->boolean('wishes_enabled')->default(false);
            $table->boolean('gifts_enabled')->default(false);
            $table->boolean('dress_code_enabled')->default(false);
            $table->boolean('countdown_enabled')->default(true);

            // Wishes moderation — off means guest submissions appear
            // immediately; on means EITHER tenant OR client can approve.
            $table->boolean('wishes_require_approval')->default(true);

            // Dress code / color palette
            $table->json('dress_code_colors')->nullable(); // [{name, hex}]
            $table->text('dress_code_note')->nullable();

            // Venue address gating — the confirmed-guest-only mechanic
            $table->boolean('gate_venue_address')->default(false);

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('event_microsite_settings'); }
};