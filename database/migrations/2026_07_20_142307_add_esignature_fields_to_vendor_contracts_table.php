<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_contracts', function (Blueprint $table) {
            $table->string('signing_token')->unique()->nullable()->after('uuid');

            // Planner signature
            $table->string('planner_signature_type')->nullable()->after('created_by'); // draw|type
            $table->longText('planner_signature_data')->nullable(); // base64 png or typed name
            $table->string('planner_signature_name')->nullable();
            $table->timestamp('planner_signed_at')->nullable();
            $table->string('planner_signed_ip')->nullable();

            // Vendor signature
            $table->string('vendor_signature_type')->nullable(); // draw|type
            $table->longText('vendor_signature_data')->nullable();
            $table->string('vendor_signature_name')->nullable();
            $table->timestamp('vendor_signed_at')->nullable();
            $table->string('vendor_signed_ip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_contracts', function (Blueprint $table) {
            $table->dropColumn([
                'signing_token',
                'planner_signature_type', 'planner_signature_data', 'planner_signature_name', 'planner_signed_at', 'planner_signed_ip',
                'vendor_signature_type', 'vendor_signature_data', 'vendor_signature_name', 'vendor_signed_at', 'vendor_signed_ip',
            ]);
        });
    }
};