<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'folder_id')) {
                $table->foreignId('folder_id')->nullable()->after('documentable_id')
                    ->constrained('document_folders')->nullOnDelete();
            }
            if (!Schema::hasColumn('documents', 'uploaded_via_share_link_id')) {
                $table->foreignId('uploaded_via_share_link_id')->nullable()
                    ->constrained('document_share_links')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['uploaded_via_share_link_id']);
            $table->dropColumn(['folder_id', 'uploaded_via_share_link_id']);
        });
    }
};