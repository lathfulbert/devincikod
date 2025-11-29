<?php

use App\Core\Database\Migration;
use App\Core\Database\Blueprint;
use App\Core\Database\Schema;

class CreateWalletsTable extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->enum('status', ['active', 'frozen', 'suspended'])->default('active');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
}
