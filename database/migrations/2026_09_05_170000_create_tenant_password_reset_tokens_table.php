<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_password_reset_tokens', function (Blueprint $table) {
            $table->string('email');
            $table->unsignedBigInteger('tenant_id');
            $table->string('token', 64);
            $table->timestamp('created_at')->nullable();

            $table->primary(['tenant_id', 'email']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_password_reset_tokens');
    }
};
