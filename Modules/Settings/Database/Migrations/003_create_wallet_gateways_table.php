<?php

namespace Modules\Settings\Database\Migrations;

use App\Core\Database\Migration;

class CreateWalletGatewaysTable extends Migration
{
    public function up(): void
    {
        $this->create('wallet_gateways', function ($table) {
            $table->id();
            $table->string('name', 100); // PayDunya, CinetPay, Orange Money, Wave
            $table->string('provider_code', 50)->unique(); // paydunya, cinetpay, orange_money, wave
            $table->string('api_url', 255);
            $table->text('api_key')->nullable(); // Will be encrypted
            $table->text('api_secret')->nullable(); // Will be encrypted
            $table->text('merchant_id')->nullable(); // Will be encrypted
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('currency', 10)->default('XOF'); // XOF for West Africa
            $table->decimal('transaction_fee', 10, 2)->default(0); // Fee percentage
            $table->json('configuration')->nullable(); // Provider-specific config
            $table->timestamps();

            // Indexes
            $table->index('provider_code');
            $table->index('is_active');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('wallet_gateways');
    }
}
