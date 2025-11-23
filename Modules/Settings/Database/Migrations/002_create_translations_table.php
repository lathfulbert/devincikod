<?php

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('language', 10);
            $table->string('key', 255);
            $table->text('value');
            $table->string('module', 100)->default('general');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        // Index et contrainte unique
        $db = \App\Core\Database\Database::getInstance();
        $db->query("ALTER TABLE translations ADD UNIQUE KEY unique_translation (language, `key`)");
        $db->query("CREATE INDEX idx_translations_language ON translations(language)");
        $db->query("CREATE INDEX idx_translations_module ON translations(module)");
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
