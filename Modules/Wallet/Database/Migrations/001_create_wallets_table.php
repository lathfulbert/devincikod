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

            // Author tracking
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
}
