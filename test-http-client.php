<?php

/**
 * Simple HTTP Client Test Script
 * 
 * Run: php test-http-client.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Http\PendingRequest;

echo "\n╔════════════════════════════════════════╗\n";
echo "║   HTTP Client Test Suite              ║\n";
echo "╚════════════════════════════════════════╝\n\n";

// Test 1: Simple GET request
echo "🧪 Test 1: Simple GET request to GitHub API\n";
echo "───────────────────────────────────────────\n";
try {
    $http = new PendingRequest();
    $response = $http->get('https://api.github.com/users/github');

    if ($response->successful()) {
        $data = $response->json();
        echo "✅ SUCCESS\n";
        echo "   User: {$data['name']}\n";
        echo "   Bio: {$data['bio']}\n";
        echo "   Repos: {$data['public_repos']}\n";
    } else {
        echo "❌ FAILED: Status {$response->status()}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 2: POST request
echo "🧪 Test 2: POST request to JSONPlaceholder\n";
echo "───────────────────────────────────────────\n";
try {
    $http = new PendingRequest();
    $response = $http->post('https://jsonplaceholder.typicode.com/posts', [
        'title' => 'Test Post',
        'body' => 'This is a test',
        'userId' => 1,
    ]);

    if ($response->successful()) {
        $data = $response->json();
        echo "✅ SUCCESS\n";
        echo "   Created post ID: {$data['id']}\n";
        echo "   Title: {$data['title']}\n";
    } else {
        echo "❌ FAILED: Status {$response->status()}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 3: Custom headers
echo "🧪 Test 3: Request with custom headers\n";
echo "───────────────────────────────────────────\n";
try {
    $http = new PendingRequest();
    $response = $http->withHeaders([
        'Accept' => 'application/json',
        'User-Agent' => 'SunuFramework-Test/1.0',
    ])->get('https://httpbin.org/headers');

    if ($response->successful()) {
        $data = $response->json();
        echo "✅ SUCCESS\n";
        echo "   User-Agent sent: {$data['headers']['User-Agent']}\n";
    } else {
        echo "❌ FAILED: Status {$response->status()}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 4: Timeout
echo "🧪 Test 4: Timeout test (2s delay with 5s timeout)\n";
echo "───────────────────────────────────────────\n";
try {
    $http = new PendingRequest();
    $start = microtime(true);
    $response = $http->timeout(5)->get('https://httpbin.org/delay/2');
    $duration = round(microtime(true) - $start, 2);

    if ($response->successful()) {
        echo "✅ SUCCESS (took {$duration}s)\n";
    } else {
        echo "❌ FAILED: Status {$response->status()}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 5: Different HTTP methods
echo "🧪 Test 5: Testing different HTTP methods\n";
echo "───────────────────────────────────────────\n";
try {
    $http = new PendingRequest();

    // GET
    $response = $http->get('https://httpbin.org/get');
    echo ($response->successful() ? "✅" : "❌") . " GET request\n";

    // POST
    $response = $http->post('https://httpbin.org/post', ['key' => 'value']);
    echo ($response->successful() ? "✅" : "❌") . " POST request\n";

    // PUT
    $response = $http->put('https://httpbin.org/put', ['key' => 'value']);
    echo ($response->successful() ? "✅" : "❌") . " PUT request\n";

    // PATCH
    $response = $http->patch('https://httpbin.org/patch', ['key' => 'value']);
    echo ($response->successful() ? "✅" : "❌") . " PATCH request\n";

    // DELETE
    $response = $http->delete('https://httpbin.org/delete');
    echo ($response->successful() ? "✅" : "❌") . " DELETE request\n";
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 6: Response methods
echo "🧪 Test 6: Testing response methods\n";
echo "───────────────────────────────────────────\n";
try {
    $http = new PendingRequest();
    $response = $http->get('https://api.github.com/users/github');

    echo "Status: {$response->status()}\n";
    echo "Successful: " . ($response->successful() ? 'Yes' : 'No') . "\n";
    echo "Content-Type: {$response->header('content-type')}\n";
    echo "Body length: " . strlen($response->body()) . " bytes\n";
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║   All Tests Complete!                  ║\n";
echo "╚════════════════════════════════════════╝\n\n";
