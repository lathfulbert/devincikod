<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('email_campaigns', function ($table) {
            $table->id();
            $table->string('name', 150);
            $table->string('subject', 255);
            $table->bigInteger('template_id')->nullable();
            $table->string('from_name', 100)->nullable();
            $table->string('from_email', 255)->nullable();
            $table->string('reply_to', 255)->nullable();

            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'paused', 'failed'])->default('draft');

            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->integer('failed_count')->default(0);

            $table->decimal('total_cost', 10, 4)->default(0);
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();

            $table->bigInteger('created_by')->nullable();
            $table->json('contact_ids')->nullable();
            $table->json('segments')->nullable();
            $table->boolean('use_personalization')->default(false);

            $table->timestamps();

            $table->index('status');
            $table->index('created_by');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('email_campaigns');
    }
};
