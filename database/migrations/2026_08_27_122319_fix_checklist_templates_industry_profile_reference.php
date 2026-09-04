<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_templates', function (Blueprint $table) {
            $table->dropForeign(['event_type_id']);
            $table->dropColumn('event_type_id');
        });

        Schema::table('checklist_templates', function (Blueprint $table) {
            $table->foreignId('industry_profile_id')->nullable()->after('description')
                ->constrained('industry_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('checklist_templates', function (Blueprint $table) {
            $table->dropForeign(['industry_profile_id']);
            $table->dropColumn('industry_profile_id');
        });

        Schema::table('checklist_templates', function (Blueprint $table) {
            $table->foreignId('event_type_id')->nullable()->constrained('event_types')->nullOnDelete();
        });
    }
};