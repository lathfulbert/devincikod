<?php

namespace Modules\ApiKeys\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema;

class CreateApiKeysTable extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function ($table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('name', 100); // e.g., "Mobile App", "Production API"
            $table->string('key', 64)->unique(); // The actual API key (e.g., sk_live_...)
            $table->string('prefix', 20)->nullable(); // Key prefix for identification (e.g., sk_live, sk_test)
            $table->text('permissions')->nullable(); // JSON array of allowed permissions/scopes
            $table->string('ip_whitelist', 500)->nullable(); // Comma-separated IPs allowed to use this key
            $table->datetime('last_used_at')->nullable();
            $table->datetime('expires_at')->nullable(); // Optional expiration date
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
}
