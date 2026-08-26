<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE moodboard_items MODIFY type ENUM('image','text','color','link','file','empty','note') NOT NULL");
    }
    public function down(): void
    {
        DB::statement("ALTER TABLE moodboard_items MODIFY type ENUM('image','text','color','link','file','empty') NOT NULL");
    }
};