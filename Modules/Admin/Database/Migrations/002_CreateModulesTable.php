<?php

use App\Core\Database\Schema\Blueprint;
use App\Core\Database\Schema\Schema;

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
            $table->string('version', 20);
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_installed')->default(false);
            $table->json('manifest')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
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
