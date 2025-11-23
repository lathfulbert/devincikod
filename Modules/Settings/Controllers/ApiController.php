<?php

namespace Modules\Settings\Controllers;

use Modules\Settings\Services\SettingsService;

class ApiController
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    public function getAllSettings()
    {
        $settings = $this->settingsService->all();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $settings]);
        exit;
    }

    public function getSetting($params)
    {
        $key = $params['key'] ?? null;

        $value = $this->settingsService->get($key);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'key' => $key, 'value' => $value]);
        exit;
    }

    public function updateSetting($params)
    {
        $key = $params['key'] ?? null;
        $value = $_POST['value'] ?? null;
        $type = $_POST['type'] ?? 'string';
        $group = $_POST['group'] ?? 'general';

        $result = $this->settingsService->set($key, $value, $type, $group);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result, 'message' => $result ? 'Setting updated' : 'Failed to update']);
        exit;
    }
}
