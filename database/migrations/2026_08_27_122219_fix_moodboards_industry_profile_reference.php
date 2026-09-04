<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moodboards', function (Blueprint $table) {
            // Drop the wrong FK (was pointing at each tenant's own,
            // arbitrary event_types table — never the fixed 8 Industry
            // Profiles this was actually meant to reference).
            $table->dropForeign(['event_type_id']);
            $table->dropColumn('event_type_id');
        });

        Schema::table('moodboards', function (Blueprint $table) {
            $table->foreignId('industry_profile_id')->nullable()->after('is_template')
                ->constrained('industry_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('moodboards', function (Blueprint $table) {
            $table->dropForeign(['industry_profile_id']);
            $table->dropColumn('industry_profile_id');
        });

        Schema::table('moodboards', function (Blueprint $table) {
            $table->foreignId('event_type_id')->nullable()->constrained('event_types')->nullOnDelete();
        });
    }
};