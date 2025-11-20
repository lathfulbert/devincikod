<?php

use App\Core\Database\Migration;

/**
 * Migration: Create user_roles pivot table
 * 
 * This migration creates the pivot table for user-role many-to-many relationship.
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
        $this->create('user_roles', function($table) {
            // Foreign Keys
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreignId('role_id')
                ->constrained('roles', 'id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Composite Primary Key
            $table->multiIndex(['user_id', 'role_id']);
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropIfExists('user_roles');
    }
};
