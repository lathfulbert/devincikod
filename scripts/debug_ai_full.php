<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

$app = new Application(dirname(__DIR__));
$app->boot();

echo "=== TEST MODULE AI DEBUG ===\n\n";

// Test 1: Check if POST is being received
echo "1. Simuler POST settings...\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['default_model'] = 'gpt-4-debug-test';
$_POST['openai_api_key'] = 'sk-test1234567890';

$registry = $app->moduleManager->getRegistry();
$settingsBefore = $registry->getSettings('AI');
echo "Settings avant: " . json_encode($settingsBefore) . "\n";

// Simulate the settings save logic
$settings = $settingsBefore;
$settings['default_model'] = $_POST['default_model'];
$registry->updateSettings('AI', $settings);

$settingsAfter = $registry->getSettings('AI');
echo "Settings après: " . json_encode($settingsAfter) . "\n";

if ($settingsAfter['default_model'] === 'gpt-4-debug-test') {
    echo "✅ Settings saved successfully!\n";
} else {
    echo "❌ Settings NOT saved!\n";
}

// Test 2: Check AIManager initialization
echo "\n2. Vérifier AIManager...\n";
$manager = \App\Core\AI\AIManager::getInstance();
$client = $manager->getOpenAIClient();

if ($client) {
    echo "✅ OpenAI Client initialized\n";
    $apiKey = getenv('OPENAI_API_KEY');
    echo "API Key: " . ($apiKey ? substr($apiKey, 0, 7) . '...' : 'NOT SET') . "\n";
} else {
    echo "❌ OpenAI Client NOT initialized\n";
    echo "API Key: " . (getenv('OPENAI_API_KEY') ? 'SET' : 'NOT SET') . "\n";
}

// Test 3: Check routes
echo "\n3. Vérifier les routes AI...\n";
$routes = $app->router->getRoutes();
$aiRoutes = array_filter($routes, function ($route) {
    return strpos($route['path'], '/admin/ai') === 0;
});

echo "Routes AI trouvées: " . count($aiRoutes) . "\n";
foreach ($aiRoutes as $route) {
    echo "  - {$route['method']} {$route['path']}\n";
}

// Clean up
$registry->updateSettings('AI', $settingsBefore);
echo "\n✅ Test terminé (settings restaurés)\n";
