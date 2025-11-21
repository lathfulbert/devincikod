<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

return new class
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('version', 50); // Support for semantic versioning with metadata
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_installed')->default(false);
            $table->json('config')->nullable(); // Module configuration
            $table->timestamps(); // created_at = installation date, updated_at = last modification
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
