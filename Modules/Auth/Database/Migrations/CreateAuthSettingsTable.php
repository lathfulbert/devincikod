<?php

namespace Modules\Auth\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Blueprint;
use App\Core\Database\Schema;

class CreateAuthSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('auth_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('type')->default('string'); // string, int, bool, json
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_settings');
    }
}
