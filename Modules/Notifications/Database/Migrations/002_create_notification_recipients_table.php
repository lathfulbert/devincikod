<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create notification_recipients table
 */
return new class
{
    public function up(): void
    {
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('channels'); // ['email', 'sms', 'push']
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->tinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('notification_id');
        });
    }

    public function down(): void
    {
        Schema::drop('notification_recipients');
    }
};
