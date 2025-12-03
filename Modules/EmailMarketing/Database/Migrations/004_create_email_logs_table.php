<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('email_logs', function ($table) {
            $table->id();
            $table->bigInteger('message_id');
            $table->string('event_type', 50);
            $table->json('event_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('message_id');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('email_logs');
    }
};
