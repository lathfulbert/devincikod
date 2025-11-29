<?php

use App\Core\Database\Migration;
use App\Core\Database\Blueprint;
use App\Core\Database\Schema;

class CreateWalletTransactionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['credit', 'debit', 'refund'])->default('credit');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description');
            $table->text('metadata')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();

            $table->multiIndex(['wallet_id', 'type']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
