<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create user_notification_preferences table
 */
return new class
{
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->onDelete('cascade');
            $table->json('channels_enabled')->nullable(); // ["email", "sms", "push"]
            $table->boolean('dnd_enabled')->default(false);
            $table->time('dnd_from')->nullable();
            $table->time('dnd_to')->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('email_opt_in')->default(true);
            $table->boolean('sms_opt_in')->default(true);
            $table->boolean('push_opt_in')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('user_notification_preferences');
    }
};
