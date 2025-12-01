<?php

namespace Modules\ApiKeys\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema;

class CreateApiRequestLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function ($table) {
            $table->id();
            $table->bigInteger('api_key_id')->nullable();
            $table->bigInteger('user_id')->nullable();

            // Request details
            $table->string('endpoint', 255);
            $table->string('method', 10); // GET, POST, PUT, DELETE, etc.
            $table->string('ip_address', 45); // IPv6 compatible
            $table->text('request_headers')->nullable();
            $table->text('request_body')->nullable();

            // Response details
            $table->integer('status_code');
            $table->text('response_body')->nullable();
            $table->integer('response_time')->nullable(); // in milliseconds

            // Metadata
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->datetime('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
}
