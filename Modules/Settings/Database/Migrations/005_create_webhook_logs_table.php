<?php

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('webhook_id');
            $table->text('payload')->nullable();
            $table->text('response')->nullable();
            $table->integer('http_code')->nullable();
            $table->text('error')->nullable();
            $table->float('duration')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $db = \App\Core\Database\Database::getInstance();
        $db->query("CREATE INDEX idx_webhook_logs_webhook_id ON webhook_logs(webhook_id)");
        $db->query("CREATE INDEX idx_webhook_logs_created_at ON webhook_logs(created_at)");
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
