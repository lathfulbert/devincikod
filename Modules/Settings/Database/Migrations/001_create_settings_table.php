<?php

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255)->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'integer', 'float', 'boolean', 'json', 'array'])->default('string');
            $table->string('setting_group', 100)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // Créer les index
        $db = \App\Core\Database\Database::getInstance();
        $db->query("CREATE INDEX idx_settings_group ON settings(setting_group)");
        $db->query("CREATE INDEX idx_settings_key ON settings(`key`)");
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
