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

    // Drop table
    echo "Dropping 'modules' table...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("DROP TABLE IF EXISTS modules");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Table dropped.\n";

    // Create table
    echo "Creating 'modules' table...\n";
    $sql = "CREATE TABLE modules (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        version VARCHAR(50) NOT NULL,
        description TEXT,
        author VARCHAR(255),
        is_enabled TINYINT(1) NOT NULL DEFAULT 0,
        is_installed TINYINT(1) NOT NULL DEFAULT 0,
        config JSON,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )";
    $pdo->exec($sql);
    echo "Table created.\n";

    // Verify columns
    $stmt = $pdo->query("DESCRIBE modules");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $columns) . "\n";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
