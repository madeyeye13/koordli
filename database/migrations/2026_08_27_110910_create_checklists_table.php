<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            // Unique — enforces one-per-event at the database level, not
            // just in application logic.
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->boolean('is_client_visible')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('checklists'); }
};