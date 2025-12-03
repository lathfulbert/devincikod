<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('email_messages', function ($table) {
            $table->id();
            $table->bigInteger('campaign_id')->nullable();
            $table->bigInteger('user_id')->nullable();

            $table->string('to_email', 255);
            $table->string('to_name', 100)->nullable();
            $table->string('from_email', 255)->nullable();
            $table->string('from_name', 100)->nullable();
            $table->string('reply_to', 255)->nullable();

            $table->string('subject', 255);
            $table->text('html_content')->nullable();
            $table->text('text_content')->nullable();

            $table->string('message_id', 255)->unique()->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'failed', 'bounced'])->default('pending');

            $table->datetime('sent_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->datetime('opened_at')->nullable();
            $table->datetime('clicked_at')->nullable();
            $table->datetime('bounced_at')->nullable();

            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('cost', 10, 4)->default(0);

            $table->timestamps();

            $table->index('campaign_id');
            $table->index('user_id');
            $table->index('to_email');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('email_messages');
    }
};
