// create_event_gift_info_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_gift_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable(); // e.g. "Your presence is the greatest gift..."
            $table->json('bank_accounts')->nullable(); // [{bank_name, account_name, account_number}]
            $table->json('registry_links')->nullable(); // [{label, url}]
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('event_gift_info'); }
};