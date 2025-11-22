<?php

use App\Core\Database\Schema;
use App\Core\Database\Blueprint;

class CreateLogsTable
{
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('level')->index();
            $table->integer('level_value');
            $table->text('message');
            $table->text('context')->nullable(); // JSON
            $table->text('extra')->nullable();   // JSON
            $table->string('remote_addr')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs');
    }
}

return new CreateLogsTable;
