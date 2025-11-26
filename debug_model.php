<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\Notifications\Models\UserNotificationPreference;

$app = new Application(__DIR__);
$app->boot();

echo "Debug Model::find\n";

// 1. Truncate
$db = \App\Core\Database\Database::getInstance();
$db->query("SET FOREIGN_KEY_CHECKS=0");
$db->query("TRUNCATE TABLE user_notification_preferences");
$db->query("SET FOREIGN_KEY_CHECKS=1");

// 2. Create manually
$db->query("INSERT INTO user_notification_preferences (user_id, channels_enabled, email_opt_in) VALUES (1, '[\"email\"]', 1)");
echo "Inserted user 1 manually.\n";

// 3. Find
$prefs = UserNotificationPreference::find(1);
if ($prefs) {
    echo "Found user 1! Class: " . get_class($prefs) . "\n";
    echo "User ID: " . $prefs->user_id . "\n";
} else {
    echo "Failed to find user 1!\n";
}

// 4. Update via Model
if ($prefs) {
    echo "Updating...\n";
    $prefs->update(['email_opt_in' => false]);
    echo "Updated.\n";

    // Check DB
    $row = $db->query("SELECT * FROM user_notification_preferences WHERE user_id = 1")->fetch();
    echo "DB email_opt_in: " . $row['email_opt_in'] . "\n";
}

// 5. Test forUser (should find existing)
echo "Testing forUser(1)...\n";
try {
    $prefs2 = UserNotificationPreference::forUser(1);
    echo "forUser returned instance.\n";
} catch (\Exception $e) {
    echo "forUser failed: " . $e->getMessage() . "\n";
}
