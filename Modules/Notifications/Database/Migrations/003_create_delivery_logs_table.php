<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create delivery_logs table
 */
return new class
{
    public function up(): void
    {
        Schema::create('delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('notification_recipients')->onDelete('cascade');
            $table->string('channel', 20); // email, sms, push
            $table->string('provider', 50);
            $table->string('provider_message_id', 255)->nullable();
            $table->enum('status', ['sent', 'delivered', 'failed', 'bounced', 'complained'])->default('sent');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->tinyInteger('attempt')->default(1);
            $table->timestamp('sent_at')->useCurrent();

            $table->index(['provider', 'status', 'sent_at']);
            $table->index('recipient_id');
        });
    }

    public function down(): void
    {
        Schema::drop('delivery_logs');
    }
};
