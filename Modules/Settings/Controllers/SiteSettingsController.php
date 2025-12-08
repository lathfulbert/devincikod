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
            'site_author' => $_POST['site_author'] ?? '',
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

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $results = file_manager()->upload(['logo' => $_FILES['logo']], 'logos');
            if (!empty($results) && $results[0]['success']) {
                $this->settingsService->set('site_logo', $results[0]['path'], 'string', 'site');
            }
        }

        // Handle dark logo upload
        if (isset($_FILES['logo_dark']) && $_FILES['logo_dark']['error'] === UPLOAD_ERR_OK) {
            $results = file_manager()->upload(['logo_dark' => $_FILES['logo_dark']], 'logos');
            if (!empty($results) && $results[0]['success']) {
                $this->settingsService->set('site_logo_dark', $results[0]['path'], 'string', 'site');
            }
        }

        // Handle logo icon upload
        if (isset($_FILES['logo_icon']) && $_FILES['logo_icon']['error'] === UPLOAD_ERR_OK) {
            $results = file_manager()->upload(['logo_icon' => $_FILES['logo_icon']], 'logos');
            if (!empty($results) && $results[0]['success']) {
                $this->settingsService->set('site_logo_icon', $results[0]['path'], 'string', 'site');
            }
        }

        // Handle favicon upload
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $results = file_manager()->upload(['favicon' => $_FILES['favicon']], 'logos');
            if (!empty($results) && $results[0]['success']) {
                $this->settingsService->set('site_favicon', $results[0]['path'], 'string', 'site');
            }
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

        // Upload using FileManager
        $results = file_manager()->upload(['logo' => $file], 'logos');

        if (!empty($results) && $results[0]['success']) {
            $logoPath = $results[0]['path'];
            $this->settingsService->set('site_logo', $logoPath, 'string', 'site');

            $_SESSION['flash_success'] = 'Logo téléchargé avec succès.';
        } else {
            $error = $results[0]['error'] ?? 'Erreur inconnue';
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement du fichier : ' . $error;
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

        // Upload using FileManager
        $results = file_manager()->upload(['favicon' => $file], 'logos');

        if (!empty($results) && $results[0]['success']) {
            $faviconPath = $results[0]['path'];
            $this->settingsService->set('site_favicon', $faviconPath, 'string', 'site');

            $_SESSION['flash_success'] = 'Favicon téléchargé avec succès.';
        } else {
            $error = $results[0]['error'] ?? 'Erreur inconnue';
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement du fichier : ' . $error;
        }

        redirect('/admin/settings/site');
    }
}
