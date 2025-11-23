<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

$app = new Application(dirname(__DIR__));
$app->boot();

$registry = $app->moduleManager->getRegistry();
$aiModule = $registry->find('AI');

echo "--- AI Module Status ---\n";
if ($aiModule) {
    echo "Module found.\n";
    echo "Enabled: " . ($aiModule['is_enabled'] ? 'Yes' : 'No') . "\n";
    echo "Config (Raw): " . $aiModule['config'] . "\n";

    $settings = $registry->getSettings('AI');
    echo "Settings (Parsed): " . print_r($settings, true) . "\n";
} else {
    echo "Module NOT found.\n";
}

echo "\n--- Environment Variable ---\n";
$apiKey = getenv('OPENAI_API_KEY');
if ($apiKey) {
    echo "OPENAI_API_KEY is set (Length: " . strlen($apiKey) . ")\n";
    echo "Preview: " . substr($apiKey, 0, 5) . "..." . substr($apiKey, -4) . "\n";
} else {
    echo "OPENAI_API_KEY is NOT set.\n";
}

echo "\n--- Testing Registry Update ---\n";
$newSettings = ['default_model' => 'gpt-4-test-verification'];
$registry->updateSettings('AI', $newSettings);
echo "Updated settings to: " . json_encode($newSettings) . "\n";

$updatedSettings = $registry->getSettings('AI');
echo "Read back settings: " . print_r($updatedSettings, true) . "\n";

// Revert
if ($aiModule) {
    $registry->updateSettings('AI', json_decode($aiModule['config'], true));
    echo "Reverted settings.\n";
}
