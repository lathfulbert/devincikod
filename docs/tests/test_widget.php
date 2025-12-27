<?php
require __DIR__ . '/bootstrap.php';

try {
    $widget = new \Modules\Dashboard\Widgets\WelcomeWidget();
    echo "✅ Widget instantiated successfully\n";

    $data = $widget->getData();
    echo "✅ Widget data retrieved successfully\n";
    echo "Title: " . ($data['title'] ?? 'N/A') . "\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}