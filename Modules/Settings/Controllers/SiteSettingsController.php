<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Services\SettingsService;

class SiteSettingsController
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    /**
     * Show site settings form
     */
    public function index()
    {
        $app = Application::getInstance();

        $settings = $this->settingsService->getSiteSettings();

        $timezones = timezone_identifiers_list();
        $languages = [
            'fr' => 'Français',
            'en' => 'English',
            'ar' => 'العربية',
            'es' => 'Español',
            'de' => 'Deutsch'
        ];

        echo view('settings/site', [
            'title' => 'Paramètres du Site',
            'settings' => $settings,
            'timezones' => $timezones,
            'languages' => $languages,
        ]);
    }

    /**
     * Update site settings
     */
    public function update()
    {
        $app = Application::getInstance();

        $settings = [
            'site_name' => $_POST['site_name'] ?? '',
            'site_description' => $_POST['site_description'] ?? '',
            'default_language' => $_POST['default_language'] ?? 'fr',
            'default_timezone' => $_POST['default_timezone'] ?? 'Africa/Dakar',
            'date_format' => $_POST['date_format'] ?? 'Y-m-d',
            'time_format' => $_POST['time_format'] ?? 'H:i',
            'items_per_page' => $_POST['items_per_page'] ?? '20',
            'maintenance_mode' => $_POST['maintenance_mode'] ?? '0',
            'registration_enabled' => $_POST['registration_enabled'] ?? '1',
        ];

        foreach ($settings as $key => $value) {
            $type = in_array($key, ['maintenance_mode', 'registration_enabled']) ? 'boolean' : 'string';
            $type = in_array($key, ['items_per_page']) ? 'integer' : $type;

            $this->settingsService->set($key, $value, $type, 'site');
        }

        $_SESSION['flash_success'] = 'Paramètres du site mis à jour avec succès.';
        redirect('/admin/settings');
    }

    /**
     * Upload logo
     */
    public function uploadLogo()
    {
        $app = Application::getInstance();

        if (!isset($_FILES['logo'])) {
            $_SESSION['flash_error'] = 'Aucun fichier sélectionné.';
            redirect('/admin/settings/site');
        }

        $file = $_FILES['logo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Erreur lors du téléchargement du fichier.';
            redirect('/admin/settings/site');
        }

        // Validate image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['flash_error'] = 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou SVG.';
            redirect('/admin/settings/site');
        }

        // Create uploads directory if not exists
        $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/logos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo-' . time() . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $logoPath = '/uploads/logos/' . $filename;
            $this->settingsService->set('site_logo', $logoPath, 'string', 'site');

            $_SESSION['flash_success'] = 'Logo téléchargé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement du fichier.';
        }

        redirect('/admin/settings/site');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon()
    {
        $app = Application::getInstance();

        if (!isset($_FILES['favicon'])) {
            $_SESSION['flash_error'] = 'Aucun fichier sélectionné.';
            redirect('/admin/settings/site');
        }

        $file = $_FILES['favicon'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Erreur lors du téléchargement du fichier.';
            redirect('/admin/settings/site');
        }

        // Validate icon
        $allowedTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['flash_error'] = 'Type de fichier non autorisé. Utilisez ICO ou PNG.';
            redirect('/admin/settings/site');
        }

        // Create uploads directory if not exists
        $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/logos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'favicon-' . time() . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $faviconPath = '/uploads/logos/' . $filename;
            $this->settingsService->set('site_favicon', $faviconPath, 'string', 'site');

            $_SESSION['flash_success'] = 'Favicon téléchargé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement du fichier.';
        }

        redirect('/admin/settings/site');
    }
}
