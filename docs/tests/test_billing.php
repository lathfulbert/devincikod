<?php

require_once __DIR__ . '/preload.php';
require_once __DIR__ . '/Modules/Wallet/Services/WalletService.php';
require_once __DIR__ . '/Modules/Wallet/Services/TransactionService.php';
require_once __DIR__ . '/Modules/Wallet/Services/PricingService.php';
require_once __DIR__ . '/Modules/Wallet/Services/BillingService.php';

use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Services\TransactionService;
use Modules\Wallet\Services\PricingService;
use Modules\Wallet\Services\BillingService;

try {
    echo "Testing Complete Wallet System with Billing...\n\n";

    // Setup
    $walletService = new WalletService();
    $txnService = new TransactionService($walletService);
    $pricingService = new PricingService();
    $billingService = new BillingService($walletService, $txnService, $pricingService);

    // Create wallet with initial balance
    echo "===== Setup =====\n";
    $walletService->createWallet(1, 10.00);
    echo "Created wallet with balance: $10.00\n\n";

    // Test pricing
    echo "===== Pricing Tests =====\n";
    echo "Default price: $" . $pricingService->calculateCost([]) . " per SMS\n";
    echo "US price: $" . $pricingService->calculateCost(['country' => 'US']) . " per SMS\n";
    echo "FR price: $" . $pricingService->calculateCost(['country' => 'FR']) . " per SMS\n";
    echo "MockGateway price: $" . $pricingService->calculateCost(['gateway' => 'MockGateway']) . " per SMS\n\n";

    // Test billing
    echo "===== Billing Tests =====\n";

    // Check if can send
    $canSend = $billingService->canSendSms(1, 100, ['country' => 'US']);
    echo "Can send 100 SMS to US? " . ($canSend ? 'Yes' : 'No') . "\n";

    // Estimate cost
    $estimate = $billingService->estimateCost(100, ['country' => 'US']);
    echo "Estimated cost for 100 SMS to US: $" . $estimate . "\n\n";

    // Charge for SMS
    echo "Charging for 50 SMS to US...\n";
    $charge = $billingService->chargeSms(1, 50, ['country' => 'US']);
    echo "Charged: $" . $charge['charged'] . "\n";
    echo "Remaining balance: $" . $charge['remaining_balance'] . "\n\n";

    // Try to send more than balance allows
    echo "Trying to charge for 200 SMS (should fail)...\n";
    try {
        $billingService->chargeSms(1, 200, ['country' => 'US']);
    } catch (\Throwable $e) {
        echo "Error (expected): " . $e->getMessage() . "\n\n";
    }

    // Add more credits
    echo "Adding $5.00 credits...\n";
    $txnService->createTransaction(1, 'credit', 5.00, 'Top-up');
    echo "New balance: $" . $walletService->getBalance(1) . "\n\n";

    // Charge again
    echo "Charging for 100 SMS to FR...\n";
    $charge = $billingService->chargeSms(1, 100, ['country' => 'FR']);
    echo "Charged: $" . $charge['charged'] . "\n";
    echo "Remaining balance: $" . $charge['remaining_balance'] . "\n\n";

    // Transaction history
    echo "===== Transaction History =====\n";
    $transactions = $txnService->getTransactions(1);
    foreach ($transactions as $txn) {
        echo "{$txn['created_at']} | {$txn['type']} | $" . $txn['amount'] . " | {$txn['description']} | {$txn['status']}\n";
    }

    echo "\n✓ All wallet system tests passed!\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
