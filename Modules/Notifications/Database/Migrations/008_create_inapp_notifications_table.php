<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create inapp_notifications table
 */
return new class
{
    public function up(): void
    {
        Schema::create('inapp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('notification_id')->nullable()->constrained('notifications')->onDelete('set null');
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 50)->default('info'); // info, success, warning, error
            $table->timestamp('read_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::drop('inapp_notifications');
    }
};
