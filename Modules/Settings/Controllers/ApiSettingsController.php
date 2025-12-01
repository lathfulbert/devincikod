<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Services\SettingsService;

class ApiSettingsController
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    public function index()
    {
        $app = Application::getInstance();
        $settings = $this->settingsService->getApiSettings();

        echo view('settings/api', [
            'title' => 'Paramètres API',
            'settings' => $settings,
        ]);
    }

    public function update()
    {
        $app = Application::getInstance();

        $settings = [
            'openai_api_key' => $_POST['openai_api_key'] ?? '',
            'google_api_key' => $_POST['google_api_key'] ?? '',
            'stripe_api_key' => $_POST['stripe_api_key'] ?? '',
            'paypal_client_id' => $_POST['paypal_client_id'] ?? '',
            'sms_api_key' => $_POST['sms_api_key'] ?? '',
            'map_api_key' => $_POST['map_api_key'] ?? '',
        ];

        foreach ($settings as $key => $value) {
            $this->settingsService->set($key, $value, 'string', 'api');
        }

        $_SESSION['flash_success'] = 'Clés API mises à jour avec succès.';
        redirect('/admin/settings');
    }

    public function testConnection()
    {
        $service = $_POST['service'] ?? '';
        $apiKey = $_POST['api_key'] ?? '';

        // Test API connection based on service
        $result = $this->testApiConnection($service, $apiKey);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    protected function testApiConnection(string $service, string $apiKey): array
    {
        switch ($service) {
            case 'openai':
                return $this->testOpenAI($apiKey);
            case 'google':
                return $this->testGoogle($apiKey);
            default:
                return ['success' => false, 'message' => 'Service non supporté'];
        }
    }

    protected function testOpenAI(string $apiKey): array
    {
        // Simple test to OpenAI API
        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode === 200,
            'message' => $httpCode === 200 ? 'Connexion réussie' : 'Échec de la connexion'
        ];
    }

    protected function testGoogle(string $apiKey): array
    {
        return ['success' => true, 'message' => 'Test Google API non implémenté'];
    }

    public function generateKey()
    {
        $app = Application::getInstance();

        $key = bin2hex(random_bytes(32));

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'key' => $key]);
        exit;
    }
}
