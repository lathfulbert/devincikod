<?php

require_once __DIR__ . '/preload.php';
require_once __DIR__ . '/Modules/Wallet/Services/WalletService.php';
require_once __DIR__ . '/Modules/Wallet/Services/TransactionService.php';

use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Services\TransactionService;

try {
    echo "Testing Wallet Module...\n\n";

    // Setup
    $walletService = new WalletService();
    $txnService = new TransactionService($walletService);

    // Test 1: Create wallet
    echo "Test 1: Creating wallet for user 1\n";
    $wallet = $walletService->createWallet(1, 100.00);
    echo "Wallet ID: {$wallet['id']}, Balance: {$wallet['balance']}\n\n";

    // Test 2: Add credit via transaction
    echo "Test 2: Adding 50 credits\n";
    $txn = $txnService->createTransaction(1, 'credit', 50.00, 'Top-up');
    echo "Transaction: {$txn['id']}, Status: {$txn['status']}\n";
    echo "New balance: " . $walletService->getBalance(1) . "\n\n";

    // Test 3: Deduct credit
    echo "Test 3: Deducting 30 credits\n";
    $txn = $txnService->createTransaction(1, 'debit', 30.00, 'SMS purchase');
    echo "Transaction: {$txn['id']}, Status: {$txn['status']}\n";
    echo "New balance: " . $walletService->getBalance(1) . "\n\n";

    // Test 4: Try to deduct more than balance
    echo "Test 4: Try to deduct 200 credits (should fail)\n";
    $txn = $txnService->createTransaction(1, 'debit', 200.00, 'Large purchase');
    echo "Transaction: {$txn['id']}, Status: {$txn['status']}\n";
    if (isset($txn['error'])) {
        echo "Error: {$txn['error']}\n";
    }
    echo "Balance unchanged: " . $walletService->getBalance(1) . "\n\n";

    // Test 5: Transaction history
    echo "Test 5: Transaction history\n";
    $transactions = $txnService->getTransactions(1);
    echo "Total transactions: " . count($transactions) . "\n";
    foreach ($transactions as $t) {
        echo "  - {$t['type']}: {$t['amount']} ({$t['status']}) - {$t['description']}\n";
    }

    echo "\nAll wallet tests passed!\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
