<?php

namespace Modules\Settings\Database\Migrations;

use App\Core\Database\Migration;

class CreateSmsGatewaysTable extends Migration
{
    public function up(): void
    {
        $this->createTable('sms_gateways', function ($table) {
            $table->id();
            $table->string('name', 100); // Orange CI, Infobip, etc.
            $table->string('provider_code', 50)->unique(); // orange_ci, infobip
            $table->string('api_url', 255);
            $table->text('api_key')->nullable(); // Will be encrypted
            $table->text('api_secret')->nullable(); // Will be encrypted
            $table->string('sender_id', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('priority')->default(0); // Higher = more priority
            $table->json('configuration')->nullable(); // Provider-specific config
            $table->timestamps();

            // Indexes
            $table->index('provider_code');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        $this->dropTable('sms_gateways');
    }
}
