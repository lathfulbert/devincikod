<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('workflows', function ($table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('trigger_type', 50);
            $table->json('steps');
            $table->enum('status', ['draft', 'active', 'paused', 'archived'])->default('draft');
            $table->integer('total_executions')->default(0);
            $table->integer('completed_executions')->default(0);
            $table->datetime('last_run_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('trigger_type');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('workflows');
    }
};
