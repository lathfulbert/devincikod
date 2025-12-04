<?php

namespace Modules\SmsCore\Database\Migrations;

use App\Core\Database\Migration;

class CreateSmsMessagesTable extends Migration
{
    public function up(): void
    {
        $this->create('sms_messages', function ($table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('to', 20); // Phone number
            $table->string('from', 20)->nullable(); // Sender ID
            $table->text('message');
            $table->string('gateway', 50); // Gateway provider code
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed
            $table->string('message_id', 100)->nullable()->unique(); // Internal message ID
            $table->string('gateway_message_id', 100)->nullable(); // Gateway's message ID
            $table->decimal('cost', 10, 4)->default(0); // Cost of the message
            $table->json('metadata')->nullable(); // Additional data
            $table->json('gateway_response')->nullable(); // Full gateway API response
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            // Author tracking columns
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('gateway');
            $table->index('message_id');
            $table->index('created_at');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('sms_messages');
    }
}
