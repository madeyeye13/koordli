<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('planner_rating_avg', 3, 2)->nullable()->after('rating');
            $table->decimal('client_rating_avg', 3, 2)->nullable()->after('planner_rating_avg');
            $table->decimal('weighted_rating', 3, 2)->nullable()->after('client_rating_avg');
            $table->unsignedInteger('completed_events_count')->default(0)->after('weighted_rating');
            $table->unsignedInteger('reviews_count')->default(0)->after('completed_events_count');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['planner_rating_avg', 'client_rating_avg', 'weighted_rating', 'completed_events_count', 'reviews_count']);
        });
    }
};