<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('event_microsite_settings', function (Blueprint $table) {
            $table->boolean('gallery_password_protected')->default(false);
            $table->string('gallery_password')->nullable();
            $table->string('gallery_password_whatsapp')->nullable();

            $table->boolean('hotels_enabled')->default(false);
            $table->text('hotels_note')->nullable();
            $table->string('hotels_maps_url')->nullable();
        });
    }
    public function down(): void {
        Schema::table('event_microsite_settings', function (Blueprint $t) {
            $t->dropColumn(['gallery_password_protected', 'gallery_password', 'gallery_password_whatsapp', 'hotels_enabled', 'hotels_note', 'hotels_maps_url']);
        });
    }
};