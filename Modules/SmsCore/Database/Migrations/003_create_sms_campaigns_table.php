<?php

use App\Core\Database\Migration;

class CreateSmsCampaignsTable extends Migration
{
    public function up(): void
    {
        $this->create('sms_campaigns', function($table) {
            $table->id();
            $table->string('name');
            $table->text('message');
            $table->string('sender_id', 20);
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'failed'])->default('draft');
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            // Author tracking columns
            $table->unsignedInteger('updated_by')->nullable();

            $table->index('status');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('sms_campaigns');
    }
}
