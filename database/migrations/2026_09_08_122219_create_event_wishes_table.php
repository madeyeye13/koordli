// create_event_wishes_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            // Whoever actually clicked approve — tenant staff or the client
            $table->enum('approved_by_type', ['tenant', 'client'])->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('event_wishes'); }
};