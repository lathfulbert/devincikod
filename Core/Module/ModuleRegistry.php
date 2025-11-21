<?php

namespace App\Core\Module;

use App\Core\Database\Database;

/**
 * Class ModuleRegistry
 * 
 * Responsible for persisting module state in database (SRP).
 * Handles module registration, activation status, and metadata storage.
 */
class ModuleRegistry
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Register a module in the database.
     * 
     * @param ModuleContract $module
     * @param ModuleManifest $manifest
     */
    public function register(ModuleContract $module, ModuleManifest $manifest): void
    {
        $existing = $this->find($module->getName());

        if ($existing) {
            // Update existing module
            $this->db->query(
                "UPDATE modules SET 
                    version = ?, 
                    description = ?, 
                    author = ?, 
                    updated_at = NOW()
                WHERE name = ?",
                [
                    $module->getVersion(),
                    $module->getDescription(),
                    $module->getAuthor(),
                    $module->getName()
                ]
            );
        } else {
            // Insert new module
            $isEnabled = $module->getName() === 'Admin' ? 1 : 0;

            $this->db->query(
                "INSERT INTO modules (name, version, description, author, is_enabled, is_installed, config, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $module->getName(),
                    $module->getVersion(),
                    $module->getDescription(),
                    $module->getAuthor(),
                    $isEnabled, // Admin enabled by default
                    1, // Marked as installed
                    json_encode([]), // Empty config
                ]
            );
        }
    }

    public function unregister(string $moduleName): void
    {
        $this->db->query("DELETE FROM modules WHERE name = ?", [$moduleName]);
    }

    public function isEnabled(string $moduleName): bool
    {
        try {
            $result = $this->db->query(
                "SELECT is_enabled FROM modules WHERE name = ?",
                [$moduleName]
            )->fetch();

            return $result ? (bool)$result['is_enabled'] : false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function isInstalled(string $moduleName): bool
    {
        try {
            $result = $this->db->query(
                "SELECT is_installed FROM modules WHERE name = ?",
                [$moduleName]
            )->fetch();

            return $result ? (bool)$result['is_installed'] : false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function setEnabled(string $moduleName, bool $enabled): void
    {
        $this->db->query(
            "UPDATE modules SET is_enabled = ?, updated_at = NOW() WHERE name = ?",
            [$enabled ? 1 : 0, $moduleName]
        );
    }

    public function setInstalled(string $moduleName, bool $installed): void
    {
        $this->db->query(
            "UPDATE modules SET is_installed = ?, updated_at = NOW() WHERE name = ?",
            [$installed ? 1 : 0, $moduleName]
        );
    }

    public function getAll(): array
    {
        try {
            return $this->db->query("SELECT * FROM modules ORDER BY name")->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getEnabled(): array
    {
        try {
            return $this->db->query("SELECT * FROM modules WHERE is_enabled = 1 ORDER BY name")->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getInstalled(): array
    {
        try {
            return $this->db->query("SELECT * FROM modules WHERE is_installed = 1 ORDER BY name")->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function find(string $moduleName): ?array
    {
        try {
            $result = $this->db->query(
                "SELECT * FROM modules WHERE name = ?",
                [$moduleName]
            )->fetch();

            return $result ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function updateSettings(string $moduleName, array $settings): void
    {
        $this->db->query(
            "UPDATE modules SET config = ?, updated_at = NOW() WHERE name = ?",
            [json_encode($settings), $moduleName]
        );
    }

    public function getSettings(string $moduleName): array
    {
        try {
            $result = $this->db->query(
                "SELECT config FROM modules WHERE name = ?",
                [$moduleName]
            )->fetch();

            if ($result && $result['config']) {
                return json_decode($result['config'], true) ?? [];
            }

            return [];
        } catch (\PDOException $e) {
            return [];
        }
    }
}
