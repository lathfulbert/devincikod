<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create notifications table
 */
return new class
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->json('data');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->tinyInteger('priority')->default(5);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::drop('notifications');
    }
};
