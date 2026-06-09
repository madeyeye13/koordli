<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->nullable();
            $table->string('entity_type'); // event|task|vendor|guest
            $table->timestamps();

            $table->unique(['tenant_id', 'name', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_labels');
    }
};