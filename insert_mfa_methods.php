<?php
require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

use App\Core\Support\DotEnv;
use App\Core\Config\Config;
use App\Core\Database\Database;

(new DotEnv('.env'))->load();
$config = new Config();
$config->load('config/database.php', 'database');
$db = Database::getInstance();
$db->connect($config->get('database.connections.mysql'));

echo "Structure de la table mfa_methods:\n\n";
$stmt = $db->query('DESCRIBE mfa_methods');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n\nInsertion des méthodes MFA...\n";
$db->query("INSERT INTO mfa_methods (type, provider_class, is_active) VALUES 
    ('totp', 'Modules\\\\Auth\\\\Providers\\\\TotpProvider', 1),
    ('sms', 'Modules\\\\Auth\\\\Providers\\\\SmsOtpProvider', 1),
    ('email', 'Modules\\\\Auth\\\\Providers\\\\EmailOtpProvider', 1)
");

echo "✅ Méthodes MFA insérées!\n\n";
echo "Vérification:\n";
$stmt = $db->query('SELECT * FROM mfa_methods');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['type']} (Active: {$row['is_active']})\n";
}
