<?php

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Migration pour créer la table de configuration du cache
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache_config', function (Blueprint $table) {
            $table->id();

            // Configuration générale
            $table->string('driver', 50)->default('filesystem'); // filesystem, redis, memcached, apcu
            $table->boolean('enabled')->default(true);
            $table->string('prefix', 100)->default('cache_');
            $table->integer('default_ttl')->default(3600); // TTL par défaut en secondes

            // Configuration Filesystem
            $table->string('filesystem_path')->default('storage/cache');

            // Configuration Redis
            $table->string('redis_host', 255)->default('127.0.0.1');
            $table->integer('redis_port')->default(6379);
            $table->string('redis_password')->nullable();
            $table->integer('redis_database')->default(0);

            // Configuration Memcached (JSON array de serveurs)
            $table->text('memcached_servers')->nullable();

            // Configuration APCu
            $table->boolean('apcu_enabled')->default(false);

            $table->timestamps();
        });

        // Insérer la configuration par défaut
        $db = \App\Core\Database\Database::getInstance();
        $db->query("
            INSERT INTO cache_config 
            (driver, enabled, prefix, default_ttl, filesystem_path, redis_host, redis_port, redis_database, memcached_servers, apcu_enabled, created_at, updated_at) 
            VALUES 
            ('filesystem', 1, 'cache_', 3600, 'storage/cache', '127.0.0.1', 6379, 0, '[{\"host\":\"127.0.0.1\",\"port\":11211}]', 0, NOW(), NOW())
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_config');
    }
};
