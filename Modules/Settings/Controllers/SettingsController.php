<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Services\SettingsService;
use Modules\Settings\Models\Setting;

class SettingsController
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    /**
     * Settings dashboard - Overview
     */
    public function index()
    {
        $app = Application::getInstance();

        $settings = Setting::getAll();

        echo view('settings/index', [
            'title' => 'Configuration Générale',
            'settings' => $settings
        ]);
    }

    /**
     * Backup all settings
     */
    public function backup()
    {
        $json = $this->settingsService->export();

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="settings-backup-' . date('Y-m-d-H-i-s') . '.json"');

        echo $json;
        exit;
    }

    /**
     * Import settings from backup
     */
    public function import()
    {
        $app = Application::getInstance();

        if (!isset($_FILES['backup_file'])) {
            $_SESSION['flash_error'] = 'Aucun fichier sélectionné.';
            redirect('/admin/settings');
        }

        $file = $_FILES['backup_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Erreur lors du téléchargement du fichier.';
            redirect('/admin/settings');
        }

        $json = file_get_contents($file['tmp_name']);

        if ($this->settingsService->import($json)) {
            $_SESSION['flash_success'] = 'Configuration importée avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'importation de la configuration.';
        }

        redirect('/admin/settings');
    }
}
