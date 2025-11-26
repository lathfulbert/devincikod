<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create notification_templates table
 */
return new class
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('channel', 20); // email, sms, push, inapp
            $table->integer('version')->default(1);
            $table->string('subject', 255)->nullable();
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->string('body_sms', 500)->nullable();
            $table->string('push_title', 100)->nullable();
            $table->string('push_body', 200)->nullable();
            $table->json('variables')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['name', 'channel']);
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::drop('notification_templates');
    }
};
