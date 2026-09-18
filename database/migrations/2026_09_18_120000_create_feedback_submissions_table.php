<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('experience');
            $table->unsignedTinyInteger('navigation_rating');
            $table->unsignedTinyInteger('clarity_rating');
            $table->json('most_useful');
            $table->boolean('had_confusion');
            $table->text('confusion_details')->nullable();
            $table->text('improvement');
            $table->unsignedTinyInteger('likelihood');
            $table->unsignedTinyInteger('overall_rating');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_submissions');
    }
};
