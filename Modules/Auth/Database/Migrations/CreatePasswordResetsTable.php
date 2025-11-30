<?php

use App\Core\Database\Migration;

/**
 * Migration: Create password_resets table
 *
 * This migration creates the password_resets table for storing
 * password reset tokens.
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
        $this->create('password_resets', function($table) {
            $table->id();
            $table->string('email', 255);
            $table->string('token', 255);
            $table->datetime('expires_at');
            $table->timestamps();

            $table->index('email');
            $table->index('token');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropIfExists('password_resets');
    }
};
