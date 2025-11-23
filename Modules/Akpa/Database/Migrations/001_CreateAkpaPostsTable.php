<?php

namespace Modules\Akpa\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema\Schema;
use App\Core\Database\Schema\Blueprint;

class CreateBlogPostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('slug')->unique();
            $table->integer('author_id');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
}
