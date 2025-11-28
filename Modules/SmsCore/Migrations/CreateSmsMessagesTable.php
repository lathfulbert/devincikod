<?php

namespace Modules\SmsCore\Migrations;

use Core\Database\Migration;
use Core\Database\Schema;
use Core\Database\Blueprint;

class CreateSmsMessagesTable extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('to', 20);
            $table->string('from', 20);
            $table->text('message');
            $table->string('gateway', 50);
            $table->enum('status', ['pending', 'queued', 'sent', 'delivered', 'failed'])->default('pending');
            $table->string('message_id')->nullable()->unique();
            $table->string('gateway_message_id')->nullable();
            $table->decimal('cost', 10, 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('gateway');
            $table->index('scheduled_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
}
