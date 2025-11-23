<?php

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_history', function (Blueprint $table) {
            $table->id();
            $table->integer('translation_id');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->integer('changed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $db = \App\Core\Database\Database::getInstance();
        $db->query("CREATE INDEX idx_translation_history_translation_id ON translation_history(translation_id)");
        $db->query("CREATE INDEX idx_translation_history_changed_by ON translation_history(changed_by)");
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_history');
    }
};
