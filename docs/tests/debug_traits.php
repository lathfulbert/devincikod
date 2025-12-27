<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

echo "🔍 Debugging SmsMessage Traits\n";
echo str_repeat("=", 60) . "\n\n";

$class = 'Modules\SmsCore\Models\SmsMessage';

echo "Class: $class\n\n";

// Direct traits
$traits = class_uses($class);
echo "Direct traits:\n";
foreach ($traits as $trait) {
    echo "  - $trait\n";
}

// Check if SoftDeletes is used
$usesSoftDeletes = in_array('App\Core\Database\Traits\SoftDeletes', class_uses($class));
echo "\nUses SoftDeletes: " . ($usesSoftDeletes ? 'YES' : 'NO') . "\n";

// Check if HasAuthor is used
$usesHasAuthor = in_array('App\Core\Database\Traits\HasAuthor', class_uses($class));
echo "Uses HasAuthor: " . ($usesHasAuthor ? 'YES' : 'NO') . "\n";

// Check table columns
echo "\n" . str_repeat("=", 60) . "\n";
echo "Table columns:\n";

$db = \App\Core\Database\Database::getInstance()->getPdo();
$stmt = $db->query("SHOW COLUMNS FROM sms_messages");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}
