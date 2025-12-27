<?php

/**
 * Test MFA Setup - Debug Script
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Support\DotEnv;
use App\Core\Config\Config;
use App\Core\Database\Database;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>MFA Setup Debug</h1>";

// Check session
echo "<h2>1. Session Status</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? '✅ Yes' : '❌ No') . "<br>";
echo "user_id in session: " . (isset($_SESSION['user_id']) ? '✅ ' . $_SESSION['user_id'] : '❌ Not set') . "<br>";

// Load environment
(new DotEnv(__DIR__ . '/.env'))->load();

// Load config
$config = new Config();
$config->load(__DIR__ . '/config/database.php', 'database');

// Connect to database
$defaultConnection = $config->get('database.default', 'mysql');
$dbConfig = $config->get("database.connections.{$defaultConnection}", []);
Database::getInstance()->connect($dbConfig);

echo "<h2>2. Database Connection</h2>";
echo "✅ Connected to database<br>";

// Check if MFA tables exist
echo "<h2>3. MFA Tables</h2>";
$db = Database::getInstance();

$tables = ['mfa_methods', 'user_mfa_setup', 'oauth_accounts', 'auth_logs'];
foreach ($tables as $table) {
    try {
        $db->query("SELECT 1 FROM $table LIMIT 1");
        echo "✅ Table <code>$table</code> exists<br>";
    } catch (Exception $e) {
        echo "❌ Table <code>$table</code> missing<br>";
    }
}

// Check MFA methods
echo "<h2>4. Available MFA Methods</h2>";
try {
    $stmt = $db->query("SELECT * FROM mfa_methods");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($methods)) {
        echo "⚠️ No MFA methods configured in database<br>";
        echo "<p><strong>Creating default MFA methods...</strong></p>";

        // Insert default methods
        $db->query("INSERT INTO mfa_methods (type, name, is_active) VALUES 
            ('totp', 'Google Authenticator (TOTP)', 1),
            ('sms', 'SMS OTP', 1),
            ('email', 'Email OTP', 1)
        ");

        echo "✅ Default MFA methods created<br>";
    } else {
        echo "✅ MFA methods configured:<br>";
        foreach ($methods as $method) {
            echo "- <code>{$method['type']}</code> ({$method['name']}) - " . ($method['is_active'] ? '✅ Active' : '❌ Inactive') . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test MfaManager if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<h2>5. User MFA Setup</h2>";

    try {
        $mfaManager = new \Modules\Auth\Services\MfaManager();
        $userId = $_SESSION['user_id'];

        $isRequired = $mfaManager->isRequired($userId);
        echo "MFA required: " . ($isRequired ? '✅ Yes' : '❌ No') . "<br>";

        $availableMethods = $mfaManager->getAvailableMethods($userId);
        echo "Available methods for user:<br>";
        if (empty($availableMethods)) {
            echo "⚠️ No MFA methods activated yet<br>";
        } else {
            foreach ($availableMethods as $method) {
                echo "- <code>{$method['type']}</code><br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<h2>5. User Status</h2>";
    echo "⚠️ You are not logged in. Please <a href='auth/login'>login first</a><br>";
}

echo "<h2>6. Test Form Submission</h2>";
echo '<form method="POST" action="/auth/mfa/setup" style="border: 1px solid #ccc; padding: 20px; background: #f5f5f5;">
    <input type="hidden" name="method" value="totp">
    <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
        Test: Activate TOTP
    </button>
</form>';

echo "<br><p><a href='/auth/mfa/settings'>← Back to MFA Settings</a></p>";
