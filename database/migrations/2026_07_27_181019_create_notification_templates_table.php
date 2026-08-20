<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. 'task_reminder', 'task_assigned'
            $table->string('category');
            $table->string('subject');       // for email; supports {{placeholders}}
            $table->text('body');            // supports {{placeholders}}, plain text (linkified at render time, same pattern as Support System messages)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};