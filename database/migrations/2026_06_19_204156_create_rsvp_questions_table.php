<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('rsvp_form_id')->constrained('rsvp_forms')->cascadeOnDelete();
            $table->string('label');
            $table->string('field_type'); // text|textarea|email|phone|number|dropdown|checkbox|radio|yes_no|date
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // for dropdown/radio/checkbox
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_questions');
    }
};