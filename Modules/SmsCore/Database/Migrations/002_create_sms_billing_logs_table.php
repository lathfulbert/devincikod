<?php

use App\Core\Database\Migration;
use App\Core\Database\Blueprint;
use App\Core\Database\Schema;

class CreateSmsBillingLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('sms_billing_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('sender_id');
            $table->string('recipient');
            $table->string('country_code', 5)->nullable();
            $table->string('operator', 50)->nullable();
            $table->string('gateway', 50)->nullable();
            $table->enum('sms_type', ['text', 'otp', 'marketing'])->default('text');
            $table->integer('segments')->default(1);
            $table->decimal('unit_cost', 10, 4)->default(0);
            $table->decimal('total_cost', 10, 4)->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamps();

            $table->multiIndex(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_billing_logs');
    }
}
