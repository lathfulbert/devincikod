<?php

use App\Core\Database\Migration;

class CreateSmsQueueTable extends Migration
{
    public function up(): void
    {
        $this->create('sms_queue', function($table) {
            $table->id();
            $table->integer('campaign_id')->nullable();
            $table->string('recipient', 20);
            $table->text('message');
            $table->string('sender_id', 20);
            $table->enum('status', ['pending', 'processing', 'sent', 'failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('sms_queue');
    }
}
