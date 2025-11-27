<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

/**
 * Create backups table
 */
return new class
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // database, files, full
            $table->string('path', 255);
            $table->string('filename', 255);
            $table->string('disk', 50)->default('local');
            $table->bigInteger('size')->default(0);
            $table->string('status', 20)->default('pending'); // pending, completed, failed
            $table->string('initiated_by', 50)->default('system'); // system, user:ID
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // $table->index('status');
            // $table->index('type');
            // $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::drop('backups');
    }
};
