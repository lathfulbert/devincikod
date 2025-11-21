<?php

$host = '127.0.0.1';
$db   = 'sunuframework2';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected to database.\n";

    $modulesToEnable = ['Auth', 'RBAC'];

    foreach ($modulesToEnable as $moduleName) {
        // Check if module exists
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE name = ?");
        $stmt->execute([$moduleName]);
        $module = $stmt->fetch();

        if ($module) {
            echo "Module '$moduleName' found. Enabling...\n";
            $pdo->prepare("UPDATE modules SET is_enabled = 1 WHERE name = ?")->execute([$moduleName]);
            echo "Module '$moduleName' ENABLED.\n";
        } else {
            echo "Module '$moduleName' NOT found in database. Inserting...\n";
            // Insert with default values
            $pdo->prepare("INSERT INTO modules (name, version, description, author, is_enabled, is_installed, config, created_at, updated_at)
                VALUES (?, '1.0.0', 'Auto-enabled module', 'System', 1, 1, '{}', NOW(), NOW())")
                ->execute([$moduleName]);
            echo "Module '$moduleName' INSERTED and ENABLED.\n";
        }
    }
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
