<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // e.g. 'task_overdue', 'invoice_due'
            $table->string('category');                // tasks|runsheets|vendors|contracts|invoices|bookings|consultations|rsvp|support|discussions|finance
            $table->string('notification_type');       // e.g. 'task_assigned', 'task_overdue' — used to resolve template + preferences
            $table->string('pattern')->default('once'); // once|daily|every_12h|every_6h|hourly|custom
            $table->unsignedInteger('custom_interval_minutes')->nullable();
            $table->json('offsets')->nullable();        // e.g. [-10080, -4320, -1440, 0] (minutes before/after due time)
            $table->unsignedInteger('escalate_after_hours')->nullable();
            $table->string('escalate_to')->nullable();  // role name, e.g. 'tenant_owner'
            $table->string('priority')->default('normal'); // critical|high|normal|low
            $table->string('template_key');             // FK-by-string to notification_templates.key
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_rules');
    }
};