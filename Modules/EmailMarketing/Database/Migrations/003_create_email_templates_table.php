<?php

use App\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('email_templates', function ($table) {
            $table->id();
            $table->string('name', 150);
            $table->string('description', 500)->nullable();
            $table->string('category', 50)->nullable();
            $table->text('html_content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('category');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('email_templates');
    }
};
