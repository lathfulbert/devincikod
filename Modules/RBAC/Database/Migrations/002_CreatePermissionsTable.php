<?php

use App\Core\Database\Migration;

/**
 * Migration: Create permissions table
 * 
 * This migration creates the permissions table for RBAC system.
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
        $this->create('permissions', function($table) {
            // Primary Key
            $table->id();
            
            // Permission Information
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
        $this->dropIfExists('permissions');
    }
};
