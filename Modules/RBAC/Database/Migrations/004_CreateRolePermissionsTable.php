<?php

use App\Core\Database\Migration;

/**
 * Migration: Create role_permissions pivot table
 * 
 * This migration creates the pivot table for role-permission many-to-many relationship.
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
        $this->create('role_permissions', function($table) {
            // Foreign Keys
            $table->foreignId('role_id')
                ->constrained('roles', 'id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreignId('permission_id')
                ->constrained('permissions', 'id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Composite Primary Key
            $table->multiIndex(['role_id', 'permission_id']);
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropIfExists('role_permissions');
    }
};
