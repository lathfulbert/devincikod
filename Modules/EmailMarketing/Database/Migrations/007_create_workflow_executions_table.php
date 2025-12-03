<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('workflow_executions', function ($table) {
            $table->id();
            $table->bigInteger('workflow_id');
            $table->bigInteger('contact_id');
            $table->integer('current_step')->default(0);
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'stopped'])->default('pending');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('next_step_at')->nullable();
            $table->json('execution_data')->nullable();
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('contact_id');
            $table->index('status');
            $table->index('next_step_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('workflow_executions');
    }
};
