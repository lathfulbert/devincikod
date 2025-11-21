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
                    manifest = ?,
                    updated_at = NOW()
                WHERE name = ?",
                [
                    $module->getVersion(),
                    $module->getDescription(),
                    $module->getAuthor(),
                    json_encode($manifest->toArray()),
                    $module->getName()
                ]
            );
        } else {
            // Insert new module
            $this->db->query(
                "INSERT INTO modules (name, version, description, author, is_enabled, is_installed, manifest, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $module->getName(),
                    $module->getVersion(),
                    $module->getDescription(),
                    $module->getAuthor(),
                    0, // Disabled by default
                    1, // Marked as installed
                    json_encode($manifest->toArray())
                ]
            );
        }
    }

    /**
     * Unregister a module from the database.
     * 
     * @param string $moduleName
     */
    public function unregister(string $moduleName): void
    {
        $this->db->query("DELETE FROM modules WHERE name = ?", [$moduleName]);
    }

    /**
     * Check if a module is enabled.
     * 
     * @param string $moduleName
     * @return bool
     */
    public function isEnabled(string $moduleName): bool
    {
        $result = $this->db->query(
            "SELECT is_enabled FROM modules WHERE name = ?",
            [$moduleName]
        )->fetch();

        return $result ? (bool)$result['is_enabled'] : false;
    }

    /**
     * Check if a module is installed.
     * 
     * @param string $moduleName
     * @return bool
     */
    public function isInstalled(string $moduleName): bool
    {
        $result = $this->db->query(
            "SELECT is_installed FROM modules WHERE name = ?",
            [$moduleName]
        )->fetch();

        return $result ? (bool)$result['is_installed'] : false;
    }

    /**
     * Set module enabled status.
     * 
     * @param string $moduleName
     * @param bool $enabled
     */
    public function setEnabled(string $moduleName, bool $enabled): void
    {
        $activatedAt = $enabled ? 'NOW()' : 'NULL';

        $this->db->query(
            "UPDATE modules SET is_enabled = ?, activated_at = {$activatedAt}, updated_at = NOW() WHERE name = ?",
            [$enabled ? 1 : 0, $moduleName]
        );
    }

    /**
     * Set module installed status.
     * 
     * @param string $moduleName
     * @param bool $installed
     */
    public function setInstalled(string $moduleName, bool $installed): void
    {
        $installedAt = $installed ? 'NOW()' : 'NULL';

        $this->db->query(
            "UPDATE modules SET is_installed = ?, installed_at = {$installedAt}, updated_at = NOW() WHERE name = ?",
            [$installed ? 1 : 0, $moduleName]
        );
    }

    /**
     * Get all registered modules.
     * 
     * @return array
     */
    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM modules ORDER BY name")->fetchAll();
    }

    /**
     * Get all enabled modules.
     * 
     * @return array
     */
    public function getEnabled(): array
    {
        return $this->db->query("SELECT * FROM modules WHERE is_enabled = 1 ORDER BY name")->fetchAll();
    }

    /**
     * Get all installed modules.
     * 
     * @return array
     */
    public function getInstalled(): array
    {
        return $this->db->query("SELECT * FROM modules WHERE is_installed = 1 ORDER BY name")->fetchAll();
    }

    /**
     * Find a module by name.
     * 
     * @param string $moduleName
     * @return array|null
     */
    public function find(string $moduleName): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM modules WHERE name = ?",
            [$moduleName]
        )->fetch();

        return $result ?: null;
    }

    /**
     * Update module settings.
     * 
     * @param string $moduleName
     * @param array $settings
     */
    public function updateSettings(string $moduleName, array $settings): void
    {
        $this->db->query(
            "UPDATE modules SET settings = ?, updated_at = NOW() WHERE name = ?",
            [json_encode($settings), $moduleName]
        );
    }

    /**
     * Get module settings.
     * 
     * @param string $moduleName
     * @return array
     */
    public function getSettings(string $moduleName): array
    {
        $result = $this->db->query(
            "SELECT settings FROM modules WHERE name = ?",
            [$moduleName]
        )->fetch();

        if ($result && $result['settings']) {
            return json_decode($result['settings'], true) ?? [];
        }

        return [];
    }
}
