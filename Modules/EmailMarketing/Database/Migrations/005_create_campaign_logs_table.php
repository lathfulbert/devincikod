<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('campaign_logs', function ($table) {
            $table->id();
            $table->bigInteger('campaign_id');
            $table->enum('campaign_type', ['email', 'sms', 'multichannel', 'workflow']);
            $table->enum('channel', ['email', 'sms', 'push', 'whatsapp']);
            $table->bigInteger('contact_id')->nullable();
            $table->string('recipient_identifier', 255);
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'failed', 'bounced', 'unsubscribed']);
            $table->decimal('cost', 10, 4)->default(0);
            $table->string('gateway', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('campaign_type');
            $table->index('channel');
            $table->index('contact_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('campaign_logs');
    }
};
