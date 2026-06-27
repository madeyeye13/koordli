<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('form_submissions')->nullOnDelete();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('consultation_type')->default('physical'); // physical|virtual
            $table->string('status')->default('pending'); // pending|confirmed|cancelled
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('meeting_link')->nullable(); // for virtual
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'booking_date']);
            $table->index(['tenant_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_bookings');
    }
};