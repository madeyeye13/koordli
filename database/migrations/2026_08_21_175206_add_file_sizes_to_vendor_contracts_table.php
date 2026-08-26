<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('unsigned_file_size')->nullable()->after('unsigned_file_path');
            $table->unsignedBigInteger('signed_file_size')->nullable()->after('signed_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_contracts', function (Blueprint $table) {
            $table->dropColumn(['unsigned_file_size', 'signed_file_size']);
        });
    }
};