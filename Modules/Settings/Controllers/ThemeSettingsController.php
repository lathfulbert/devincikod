<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Services\SettingsService;

class ThemeSettingsController
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    public function index()
    {
        $app = Application::getInstance();
        $settings = $this->settingsService->getThemeSettings();

        return view('settings/theme', [
            'title' => 'Paramètres du Thème',
            'settings' => $settings,
        ]);
    }

    public function update()
    {
        $app = Application::getInstance();

        $settings = [
            'theme_mode' => $_POST['theme_mode'] ?? 'light',
            'primary_color' => $_POST['primary_color'] ?? '#7366FF',
            'secondary_color' => $_POST['secondary_color'] ?? '#838383',
            'success_color' => $_POST['success_color'] ?? '#65c15c',
            'sidebar_type' => $_POST['sidebar_type'] ?? 'compact-sidebar',
            'sidebar_icon' => $_POST['sidebar_icon'] ?? 'stroke-svg',
            'layout_type' => $_POST['layout_type'] ?? 'ltr',
            'font_family' => $_POST['font_family'] ?? 'Rubik',
            'font_size' => $_POST['font_size'] ?? '14px',
        ];

        foreach ($settings as $key => $value) {
            $this->settingsService->set($key, $value, 'string', 'theme');
        }

        $_SESSION['flash_success'] = 'Paramètres du thème mis à jour avec succès.';
        redirect('/admin/settings');
    }

    public function preview()
    {
        // Return JSON for AJAX preview
        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Preview applied']);
        exit;
    }

    public function reset()
    {
        $app = Application::getInstance();

        $defaults = [
            'theme_mode' => 'light',
            'primary_color' => '#7366FF',
            'secondary_color' => '#838383',
            'success_color' => '#65c15c',
            'sidebar_type' => 'compact-sidebar',
            'sidebar_icon' => 'stroke-svg',
            'layout_type' => 'ltr',
            'font_family' => 'Rubik',
            'font_size' => '14px',
        ];

        foreach ($defaults as $key => $value) {
            $this->settingsService->set($key, $value, 'string', 'theme');
        }

        $_SESSION['flash_success'] = 'Thème réinitialisé aux paramètres par défaut.';
        redirect('/admin/settings/theme');
    }
}
