<?php

namespace Modules\Contacts\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

class CreateContactFieldDefinitionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('contact_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->enum('type', ['text', 'number', 'date', 'select', 'textarea'])->default('text');
            $table->json('options')->nullable(); // For select type
            $table->boolean('is_required')->default(0);
            $table->string('default_value')->nullable();
            $table->integer('order')->default(0);
            $table->string('placeholder', 255)->nullable();
            $table->string('help_text', 255)->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_field_definitions');
    }
}
