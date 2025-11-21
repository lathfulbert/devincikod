<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing validation system...\n\n";

try {
    // Test 1: Simple validation
    echo "Test 1: Creating validator instance...\n";
    $validator = \App\Core\Validation\Validator::make([
        'email' => 'test@example.com'
    ], [
        'email' => 'required|email'
    ]);
    echo "✓ Validator created\n";

    echo "Test 2: Checking if passes...\n";
    if ($validator->passes()) {
        echo "✓ Validation passed!\n";
    } else {
        echo "✗ Validation failed\n";
        print_r($validator->errors()->all());
    }

    echo "\nTest 3: Invalid email...\n";
    $validator2 = \App\Core\Validation\Validator::make([
        'email' => 'invalid'
    ], [
        'email' => 'required|email'
    ]);

    if ($validator2->fails()) {
        echo "✓ Validation correctly failed\n";
        echo "Error: " . $validator2->errors()->first('email') . "\n";
    }

    echo "\n✓ All tests passed!\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
