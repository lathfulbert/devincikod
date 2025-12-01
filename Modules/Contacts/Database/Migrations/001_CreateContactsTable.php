<?php

namespace Modules\Contacts\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

class CreateContactsTable extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index('phone');
            $table->index('email');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
}
