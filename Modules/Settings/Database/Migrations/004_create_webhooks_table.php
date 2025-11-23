<?php

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('url', 500);
            $table->text('events');
            $table->string('secret', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('headers')->nullable();
            $table->integer('retry_count')->default(3);
            $table->integer('timeout')->default(30);
            $table->timestamps();
        });

        $db = \App\Core\Database\Database::getInstance();
        $db->query("CREATE INDEX idx_webhooks_is_active ON webhooks(is_active)");
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
