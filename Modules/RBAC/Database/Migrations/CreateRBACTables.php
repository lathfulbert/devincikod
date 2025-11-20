<?php

namespace Modules\RBAC\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

class CreateRBACTables extends Migration
{
    public function up(): void
    {
        // Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Unique constraint not yet implemented in Schema, but field is there
            $table->string('description')->nullable();
            $table->string('level')->default(0); // For hierarchy
            $table->timestamps();
        });

        // Permissions Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('group_name')->nullable(); // For grouping permissions
            $table->timestamps();
        });

        // User Roles Pivot
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Should be integer/foreign key
            $table->string('role_id');
            $table->timestamps();
        });

        // Role Permissions Pivot
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_id');
            $table->string('permission_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
}
