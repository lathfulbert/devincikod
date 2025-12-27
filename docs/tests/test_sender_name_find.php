<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\SmsCore\Models\SenderName;

$app = new Application(__DIR__);
$app->boot();

echo "🧪 Test SenderName::find()\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Get all sender names
    $senderNames = SenderName::all();
    echo "✅ SenderName::all() works - Found " . count($senderNames) . " sender names\n\n";

    if (!empty($senderNames)) {
        $firstId = $senderNames[0]->id;
        echo "Testing SenderName::find($firstId)...\n";

        $senderName = SenderName::find($firstId);
        if ($senderName) {
            echo "✅ SenderName::find($firstId) works\n";
            echo "   Name: {$senderName->name}\n";
            echo "   Status: {$senderName->status}\n";
            echo "   Created by: " . ($senderName->getCreatorName() ?? 'N/A') . "\n";
        } else {
            echo "⚠️  SenderName::find($firstId) returned null\n";
        }
    } else {
        echo "ℹ️  No sender names in database\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Test completed successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
