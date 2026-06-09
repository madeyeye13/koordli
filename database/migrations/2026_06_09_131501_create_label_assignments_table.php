<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('label_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('label_id')->constrained('tenant_labels')->cascadeOnDelete();
            $table->morphs('labelable'); // labelable_type, labelable_id
            $table->timestamps();

            $table->unique(['label_id', 'labelable_type', 'labelable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_assignments');
    }
};