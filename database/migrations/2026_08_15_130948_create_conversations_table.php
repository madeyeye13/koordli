<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('type'); // group|direct
            $table->string('name')->nullable(); // e.g. "Event Communication", "Planning Team" — null for direct threads
            $table->string('created_by_type'); // tenant_user (only planners/staff can create conversations)
            $table->unsignedBigInteger('created_by_id');
            $table->timestamps();

            $table->index(['event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};