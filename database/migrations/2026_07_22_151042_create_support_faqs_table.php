<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->longText('answer'); // plain text, may contain URLs — linkified at render time
            $table->json('keywords')->nullable(); // array of match terms
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_faqs');
    }
};