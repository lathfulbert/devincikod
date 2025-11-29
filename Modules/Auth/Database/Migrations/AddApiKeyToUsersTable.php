<?php

use App\Core\Database\Migration;

/**
 * Migration: Add API key to users table
 *
 * This migration adds api_key and api_key_created_at fields to users table
 * for API authentication.
 *
 * Usage: php sunu migrate
 */
return new class extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->raw("
            ALTER TABLE users
            ADD COLUMN api_key VARCHAR(64) NULL UNIQUE,
            ADD COLUMN api_key_created_at DATETIME NULL
        ");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->raw("
            ALTER TABLE users
            DROP COLUMN api_key,
            DROP COLUMN api_key_created_at
        ");
    }
};
