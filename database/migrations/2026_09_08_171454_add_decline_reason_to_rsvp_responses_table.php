// add_decline_reason_to_rsvp_responses_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rsvp_responses', function (Blueprint $table) {
            $table->text('decline_reason')->nullable()->after('status');
        });
    }
    public function down(): void {
        Schema::table('rsvp_responses', fn (Blueprint $t) => $t->dropColumn('decline_reason'));
    }
};