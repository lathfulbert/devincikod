<?php

namespace App\Core\Database;

use PDO;
use PDOException;
use App\Core\Config\Config;

class Database
{
    protected static ?Database $instance = null;
    protected PDO $pdo;
    protected string $driver = 'mysql';

    private function __construct() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function connect(array $config): void
    {
        try {
            $this->driver = $config['driver'] ?? 'mysql';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            switch ($this->driver) {
                case 'sqlite':
                    $dsn = "sqlite:{$config['database']}";
                    break;
                case 'pgsql':
                    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
                    break;
                case 'mysql':
                default:
                    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                    break;
            }

            $this->pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
