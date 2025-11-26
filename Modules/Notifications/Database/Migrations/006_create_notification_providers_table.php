<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create notification_providers table
 */
return new class
{
    public function up(): void
    {
        Schema::create('notification_providers', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // email, sms, push
            $table->string('name', 50);
            $table->string('driver', 50); // sendgrid, mailgun, twilio, fcm
            $table->json('config'); // credentials, endpoints
            $table->tinyInteger('weight')->default(10);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['type', 'name']);
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::drop('notification_providers');
    }
};
