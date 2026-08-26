<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_event_assignments', function (Blueprint $table) {
            $table->enum('selection_source', [
                'planner_selected', 'client_selected', 'planner_suggested', 'client_external',
            ])->default('planner_selected')->after('status');

            $table->enum('client_approval_status', [
                'not_required', 'pending', 'approved', 'rejected',
            ])->default('not_required')->after('selection_source');

            $table->timestamp('client_approved_at')->nullable()->after('client_approval_status');

            $table->boolean('is_client_visible')->default(false)->after('client_approved_at');
            $table->boolean('client_can_view_pricing')->default(false)->after('is_client_visible');

            $table->enum('payment_responsibility', [
                'client_pays_planner', 'client_pays_vendor_direct', 'planner_pays_from_budget',
            ])->default('planner_pays_from_budget')->after('client_can_view_pricing');

            $table->timestamp('disclaimer_acknowledged_at')->nullable()->after('payment_responsibility');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_event_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'selection_source', 'client_approval_status', 'client_approved_at',
                'is_client_visible', 'client_can_view_pricing', 'payment_responsibility',
                'disclaimer_acknowledged_at',
            ]);
        });
    }
};