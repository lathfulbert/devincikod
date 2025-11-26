<?php

/**
 * Notification System Test Script
 * 
 * Run: php test-notification-system.php
 */


require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\Notifications\Models\NotificationTemplate;
use Modules\Notifications\Models\UserNotificationPreference;
use Modules\Notifications\Services\NotificationService;

// Bootstrap application FIRST (before any output)
$app = new Application(__DIR__);
$app->boot();

// Enable implicit flush
ob_implicit_flush(true);

echo "\n╔════════════════════════════════════════╗\n";
echo "║   Notification System Test Suite      ║\n";
echo "╚════════════════════════════════════════╝\n\n";

// Test 1: Create Test Template
echo "🧪 Test 1: Creating test email template\n";
echo "───────────────────────────────────────────\n";
try {
    // Try to find existing template first
    $existingTemplate = NotificationTemplate::query()->where('name', 'test_email')->first();

    if ($existingTemplate) {
        echo "ℹ️  Template already exists (ID: {$existingTemplate->id}), skipping creation\n";
        $template = $existingTemplate;
    } else {
        $template = NotificationTemplate::create([
            'name' => 'test_email',
            'channel' => 'email',
            'subject' => 'Test Email - {{ name }}',
            'body_html' => '<h1>Hello {{ name }}!</h1><p>{{ message }}</p>',
            'body_text' => 'Hello {{ name }}! {{ message }}',
            'variables' => ['name', 'message'],
            'active' => true,
        ]);

        echo "✅ Template created successfully (ID: {$template->id})\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 2: Create User Preferences
echo "🧪 Test 2: Setting up user notification preferences\n";
echo "───────────────────────────────────────────\n";
try {
    $prefs = UserNotificationPreference::forUser(1);
    $prefs->update([
        'channels_enabled' => ['email'],
        'email_opt_in' => true,
        'dnd_enabled' => false,
    ]);

    echo "✅ User preferences configured\n";
    echo "   Channels: " . implode(', ', $prefs->channels_enabled ?? ['email']) . "\n";
    echo "   Email opt-in: " . ($prefs->email_opt_in ? 'Yes' : 'No') . "\n";
} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";
echo "Tests 1 and 2 completed.\n";
