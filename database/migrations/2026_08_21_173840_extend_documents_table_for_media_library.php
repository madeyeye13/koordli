<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old FK/column only if they still exist — safe to re-run
        // after a partially-applied prior attempt.
        if (Schema::hasColumn('documents', 'uploaded_by')) {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'documents'
                  AND COLUMN_NAME = 'uploaded_by'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if (!empty($foreignKeys)) {
                Schema::table('documents', function (Blueprint $table) use ($foreignKeys) {
                    $table->dropForeign($foreignKeys[0]->CONSTRAINT_NAME);
                });
            }

            Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('uploaded_by');
            });
        }

        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'uploaded_by_type')) {
                $table->string('uploaded_by_type')->nullable()->after('documentable_id');
            }
            if (!Schema::hasColumn('documents', 'uploaded_by_id')) {
                $table->unsignedBigInteger('uploaded_by_id')->nullable()->after('uploaded_by_type');
            }
            if (!Schema::hasColumn('documents', 'type')) {
                $table->enum('type', ['file', 'logo', 'link'])->default('file')->after('name');
            }
            if (!Schema::hasColumn('documents', 'external_url')) {
                $table->string('external_url')->nullable()->after('path');
            }
            if (!Schema::hasColumn('documents', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable()->after('path');
            }
            if (!Schema::hasColumn('documents', 'width')) {
                $table->unsignedInteger('width')->nullable();
            }
            if (!Schema::hasColumn('documents', 'height')) {
                $table->unsignedInteger('height')->nullable();
            }
            if (!Schema::hasColumn('documents', 'duration_seconds')) {
                $table->unsignedInteger('duration_seconds')->nullable();
            }
            if (!Schema::hasColumn('documents', 'compression_status')) {
                $table->enum('compression_status', ['not_applicable', 'pending', 'processing', 'completed', 'skipped', 'failed'])
                    ->default('not_applicable')->after('duration_seconds');
            }
        });

        // NOTE: folder_id and uploaded_via_share_link_id are deliberately
        // NOT added here — they reference document_folders and
        // document_share_links, which don't exist until later migrations.
        // Added instead in add_folder_and_share_link_refs_to_documents_table,
        // which must run after both of those tables are created.

        if (!Schema::hasIndex('documents', 'documents_uploaded_by_type_uploaded_by_id_index')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->index(['uploaded_by_type', 'uploaded_by_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'uploaded_by_type', 'uploaded_by_id', 'type',
                'external_url', 'thumbnail_path', 'width',
                'height', 'duration_seconds', 'compression_status',
            ]);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};