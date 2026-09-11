<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateSlugs = DB::table('rsvp_forms')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        foreach ($duplicateSlugs as $slug) {
            $rows = DB::table('rsvp_forms')->where('slug', $slug)->orderBy('id')->get();

            // First row keeps the original slug untouched. Every subsequent
            // row gets a NEW, genuinely available slug — checked against the
            // real current table state each time, not assumed from position,
            // so a generated candidate can never collide with an existing
            // real slug (including one just created earlier in this same loop).
            foreach ($rows->skip(1) as $row) {
                $suffix = 2;
                do {
                    $candidate = $slug . '-' . $suffix;
                    $exists = DB::table('rsvp_forms')->where('slug', $candidate)->exists();
                    $suffix++;
                } while ($exists);

                DB::table('rsvp_forms')->where('id', $row->id)->update(['slug' => $candidate]);
            }
        }

        Schema::table('rsvp_forms', function (Blueprint $table) {
            $table->unique('slug', 'rsvp_forms_slug_global_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rsvp_forms', function (Blueprint $table) {
            $table->dropUnique('rsvp_forms_slug_global_unique');
        });
    }
};