<?php

namespace Modules\RBAC\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema;

class CreateModulesTable extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
}
