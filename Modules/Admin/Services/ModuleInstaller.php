<?php

namespace Modules\Admin\Services;

use App\Core\Application;
use App\Core\Module\ModuleManager;
use ZipArchive;
use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Service responsible for installing, uninstalling and managing modules.
 */
class ModuleInstaller
{
    private string $modulesPath;
    private string $tempPath;
    private ModuleManager $moduleManager;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->modulesPath = $app->getBasePath() . '/Modules';
        $this->tempPath    = $app->getBasePath() . '/storage/temp';
        $this->moduleManager = $app->moduleManager;

        // Ensure temporary directory exists
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Install a module from an uploaded ZIP file.
     *
     * @param array $file $_FILES entry
     * @return array ['success' => bool, 'message' => string, 'module' => ?string]
     */
    public function installFromZip(array $file): array
    {
        try {
            // 1. Validate upload
            $validation = $this->validateUpload($file);
            if (!$validation['success']) {
                return $validation;
            }

            // 2. Extract to a temporary folder
            $extractPath = $this->tempPath . '/' . uniqid('module_');
            $extracted   = $this->extractZip($file['tmp_name'], $extractPath);
            if (!$extracted['success']) {
                return $extracted;
            }

            // 3. Validate module structure (module.json)
            $moduleInfo = $this->validateModuleStructure($extractPath);
            if (!$moduleInfo['success']) {
                $this->cleanup($extractPath);
                return $moduleInfo;
            }

            $moduleName = $moduleInfo['name'];
            $moduleDest = $this->modulesPath . '/' . $moduleName;

            // 4. Prevent overwriting an existing module
            if (is_dir($moduleDest)) {
                $this->cleanup($extractPath);
                return [
                    'success' => false,
                    'message' => "Le module '$moduleName' existe déjà. Veuillez le désinstaller d'abord."
                ];
            }

            // 5. Move extracted files to the Modules directory
            if (!rename($extractPath, $moduleDest)) {
                $this->cleanup($extractPath);
                return [
                    'success' => false,
                    'message' => "Impossible de déplacer le module vers le dossier Modules."
                ];
            }

            // 6. Register the module in the registry/database
            $this->registerModule($moduleName);

            return [
                'success' => true,
                'message' => "Module '$moduleName' installé avec succès.",
                'module'  => $moduleName
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'installation : " . $e->getMessage()
            ];
        }
    }

    /**
     * Uninstall a module completely.
     *
     * @param string $moduleName
     * @return array ['success' => bool, 'message' => string]
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
            // Let the core ModuleManager handle deactivation, rollback and registry cleanup
            $uninstalled = $this->moduleManager->uninstallModule($moduleName);

            if ($uninstalled) {
                // Remove the physical folder
                $this->cleanup($modulePath);
                return [
                    'success' => true,
                    'message' => "Module '$moduleName' désinstallé avec succès (fichiers, tables et cache supprimés)."
                ];
            }

            return [
                'success' => false,
                'message' => "Impossible de désinstaller le module '$moduleName'."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la désinstallation : " . $e->getMessage()
            ];
        }
    }

    // ---------------------------------------------------------------------
    // Helper methods (validation, extraction, cleanup, registration, etc.)
    // ---------------------------------------------------------------------

    private function validateUpload(array $file): array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'Aucun fichier uploadé.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => "Erreur lors de l'upload du fichier."];
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return ['success' => false, 'message' => 'Le fichier doit être au format ZIP.'];
        }
        if ($file['size'] > 50 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Le fichier est trop volumineux (max 50 MB).'];
        }
        return ['success' => true];
    }

    private function extractZip(string $zipPath, string $dest): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => "Impossible d'ouvrir le fichier ZIP."];
        }
        if (!$zip->extractTo($dest)) {
            $zip->close();
            return ['success' => false, 'message' => "Erreur lors de l'extraction du ZIP."];
        }
        $zip->close();
        return ['success' => true];
    }

    private function validateModuleStructure(string $path): array
    {
        $jsonPath = $this->findModuleJson($path);
        if (!$jsonPath) {
            return ['success' => false, 'message' => 'Fichier module.json introuvable. Structure de module invalide.'];
        }
        $json = json_decode(file_get_contents($jsonPath), true);
        if (!$json || !isset($json['name'])) {
            return ['success' => false, 'message' => 'Fichier module.json invalide ou champ "name" manquant.'];
        }
        // If the json lives in a sub‑folder, flatten the structure
        $dir = dirname($jsonPath);
        if ($dir !== $path) {
            $this->flattenDirectory($dir, $path);
        }
        return ['success' => true, 'name' => $json['name'], 'version' => $json['version'] ?? '1.0.0'];
    }

    private function findModuleJson(string $path): ?string
    {
        if (file_exists($path . '/module.json')) {
            return $path . '/module.json';
        }
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') continue;
            $sub = $path . '/' . $item;
            if (is_dir($sub) && file_exists($sub . '/module.json')) {
                return $sub . '/module.json';
            }
        }
        return null;
    }

    private function flattenDirectory(string $src, string $dest): void
    {
        foreach (scandir($src) as $item) {
            if ($item === '.' || $item === '..') continue;
            rename($src . '/' . $item, $dest . '/' . $item);
        }
        rmdir($src);
    }

    private function cleanup(string $path): void
    {
        if (!is_dir($path)) return;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($path);
    }

    private function registerModule(string $moduleName): void
    {
        // Refresh the module list and sync the registry – this is enough for a new module
        $this->moduleManager->discover();
        $this->moduleManager->syncToRegistry();
    }
}
