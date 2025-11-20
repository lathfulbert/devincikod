<?php

use App\Core\Database\Migration;

/**
 * Migration: Create roles table
 * 
 * This migration creates the roles table for RBAC system.
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
        $this->create('roles', function($table) {
            // Primary Key
            $table->id();
            
            // Role Information
            $table->string('name', 100);
            $table->slug('slug')->unique();
            $table->text('description')->nullable();
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropIfExists('roles');
    }
};
