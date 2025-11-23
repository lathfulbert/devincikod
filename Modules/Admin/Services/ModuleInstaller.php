<?php

namespace Modules\Admin\Services;

use App\Core\Application;
use ZipArchive;
use Exception;

class ModuleInstaller
{
    private string $modulesPath;
    private string $tempPath;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->modulesPath = $app->getBasePath() . '/Modules';
        $this->tempPath = $app->getBasePath() . '/storage/temp';

        // Ensure temp directory exists
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Install a module from uploaded ZIP file
     * 
     * @param array $file $_FILES array element
     * @return array ['success' => bool, 'message' => string, 'module' => string|null]
     */
    public function installFromZip(array $file): array
    {
        try {
            // Validate file
            $validation = $this->validateUpload($file);
            if (!$validation['success']) {
                return $validation;
            }

            // Extract to temp directory
            $extractPath = $this->tempPath . '/' . uniqid('module_');
            $extracted = $this->extractZip($file['tmp_name'], $extractPath);

            if (!$extracted['success']) {
                return $extracted;
            }

            // Validate module structure
            $moduleInfo = $this->validateModuleStructure($extractPath);
            if (!$moduleInfo['success']) {
                $this->cleanup($extractPath);
                return $moduleInfo;
            }

            $moduleName = $moduleInfo['name'];
            $moduleDestination = $this->modulesPath . '/' . $moduleName;

            // Check if module already exists
            if (is_dir($moduleDestination)) {
                $this->cleanup($extractPath);
                return [
                    'success' => false,
                    'message' => "Le module '$moduleName' existe déjà. Veuillez le désinstaller d'abord."
                ];
            }

            // Move to Modules directory
            if (!rename($extractPath, $moduleDestination)) {
                $this->cleanup($extractPath);
                return [
                    'success' => false,
                    'message' => "Impossible de déplacer le module vers le dossier Modules."
                ];
            }

            // Register in database
            $this->registerModule($moduleName);

            return [
                'success' => true,
                'message' => "Module '$moduleName' installé avec succès.",
                'module' => $moduleName
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'installation : " . $e->getMessage()
            ];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateUpload(array $file): array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'Aucun fichier uploadé.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier.'];
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            return ['success' => false, 'message' => 'Le fichier doit être au format ZIP.'];
        }

        // Check file size (max 50MB)
        if ($file['size'] > 50 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Le fichier est trop volumineux (max 50MB).'];
        }

        return ['success' => true];
    }

    /**
     * Extract ZIP file
     */
    private function extractZip(string $zipPath, string $destination): array
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => 'Impossible d\'ouvrir le fichier ZIP.'];
        }

        // Extract
        if (!$zip->extractTo($destination)) {
            $zip->close();
            return ['success' => false, 'message' => 'Erreur lors de l\'extraction du ZIP.'];
        }

        $zip->close();
        return ['success' => true];
    }

    /**
     * Validate module structure and return module info
     */
    private function validateModuleStructure(string $path): array
    {
        // Find module.json (might be in subdirectory)
        $moduleJsonPath = $this->findModuleJson($path);

        if (!$moduleJsonPath) {
            return [
                'success' => false,
                'message' => 'Fichier module.json introuvable. Structure de module invalide.'
            ];
        }

        // Parse module.json
        $moduleJson = json_decode(file_get_contents($moduleJsonPath), true);

        if (!$moduleJson || !isset($moduleJson['name'])) {
            return [
                'success' => false,
                'message' => 'Fichier module.json invalide ou champ "name" manquant.'
            ];
        }

        // If module.json is in a subdirectory, move everything up
        $moduleDir = dirname($moduleJsonPath);
        if ($moduleDir !== $path) {
            $this->flattenDirectory($moduleDir, $path);
        }

        return [
            'success' => true,
            'name' => $moduleJson['name'],
            'version' => $moduleJson['version'] ?? '1.0.0'
        ];
    }

    /**
     * Find module.json in directory or subdirectories
     */
    private function findModuleJson(string $path): ?string
    {
        // Check directly
        if (file_exists($path . '/module.json')) {
            return $path . '/module.json';
        }

        // Check in first subdirectory (common case: module-name/module.json)
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $subPath = $path . '/' . $item;
            if (is_dir($subPath) && file_exists($subPath . '/module.json')) {
                return $subPath . '/module.json';
            }
        }

        return null;
    }

    /**
     * Flatten directory structure (move contents up one level)
     */
    private function flattenDirectory(string $source, string $destination): void
    {
        $items = scandir($source);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $sourcePath = $source . '/' . $item;
            $destPath = $destination . '/' . $item;

            rename($sourcePath, $destPath);
        }

        // Remove empty source directory
        rmdir($source);
    }

    /**
     * Register module in database
     */
    private function registerModule(string $moduleName): void
    {
        $app = Application::getInstance();
        $moduleManager = $app->moduleManager;

        // Discover and sync
        $moduleManager->discover();
        $moduleManager->syncToRegistry();
    }

    /**
     * Cleanup temporary directory
     */
    private function cleanup(string $path): void
    {
        if (!is_dir($path)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($path);
    }

    /**
     * Uninstall a module
     */
    public function uninstall(string $moduleName): array
    {
        $modulePath = $this->modulesPath . '/' . $moduleName;

        if (!is_dir($modulePath)) {
            return [
                'success' => false,
                'message' => "Le module '$moduleName' n'existe pas."
            ];
        }

        try {
            // Unregister from database
            $app = Application::getInstance();
            $registry = $app->moduleManager->getRegistry();
            $registry->unregister($moduleName);

            // Delete directory
            $this->cleanup($modulePath);

            return [
                'success' => true,
                'message' => "Module '$moduleName' désinstallé avec succès."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la désinstallation : " . $e->getMessage()
            ];
        }
    }
}
