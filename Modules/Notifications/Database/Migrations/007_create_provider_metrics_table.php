<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create provider_metrics table
 */
return new class
{
    public function up(): void
    {
        Schema::create('provider_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('notification_providers')->onDelete('cascade');
            $table->integer('success_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->integer('avg_latency_ms')->default(0);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->date('date');

            $table->unique(['provider_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::drop('provider_metrics');
    }
};
