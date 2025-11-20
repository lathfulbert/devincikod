<?php

use App\Core\Database\Migration;

/**
 * Migration: Create users table
 * 
 * This migration creates the users table with all necessary fields
 * for authentication and user management using Laravel-style syntax.
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
        $this->create('users', function($table) {
            // Primary Key
            $table->id();
            
            // User Information
            $table->string('username', 100)->unique();
            $table->email('email')->unique();
            $table->password('password');
            
            // Profile
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('avatar', 255)->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropIfExists('users');
    }
};
