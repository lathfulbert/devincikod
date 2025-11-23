<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('ai_logs', function ($table) {
            $table->id();
            $table->string('module', 50);
            $table->string('agent_class', 100);
            $table->string('model', 50);
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->dropIfExists('ai_logs');
    }
};
