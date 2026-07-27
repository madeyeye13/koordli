<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industry_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // wedding_events|corporate|production|church|conference|entertainment|exhibition|other
            $table->string('name');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->json('terminology')->nullable();              // {"event":"Production","client":"Producer",...}
            $table->json('default_event_types')->nullable();      // [{"name":"Film Shoot","icon":"video","color":"#..."}]
            $table->json('default_vendor_categories')->nullable();
            $table->json('default_task_categories')->nullable();
            $table->json('default_roles')->nullable();            // ["Line Producer","1st AD","Gaffer"]
            $table->json('recommended_feature_flags')->nullable(); // ["rsvp","runsheet","vendor_portal"]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_profiles');
    }
};