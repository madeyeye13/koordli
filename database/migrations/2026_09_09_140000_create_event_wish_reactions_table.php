<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_wish_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wish_id')->constrained('event_wishes')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('reaction_type', ['heart', 'congrats']);
            $table->string('reactor_token', 128);
            $table->timestamps();

            $table->unique(['wish_id', 'reaction_type', 'reactor_token']);
            $table->index(['wish_id', 'reaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_wish_reactions');
    }
};
