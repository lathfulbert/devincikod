<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('ai_agents', function ($table) {
            $table->id();
            $table->string('module', 50)->unique();
            $table->string('agent_class', 100);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->dropIfExists('ai_agents');
    }
};
