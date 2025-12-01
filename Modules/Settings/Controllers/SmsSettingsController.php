<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Models\SmsGateway;
use Modules\Settings\Models\Setting;

class SmsSettingsController
{
    /**
     * Display SMS settings and gateways
     */
    public function index()
    {
        $app = Application::getInstance();

        $gateways = SmsGateway::all();
        $settings = Setting::getByGroup('sms');

        echo view('settings/sms/index', [
            'title' => 'Configuration SMS',
            'gateways' => $gateways,
            'settings' => $settings
        ]);
    }

    /**
     * Show form to create new gateway
     */
    public function createGateway()
    {
        $app = Application::getInstance();

        echo view('settings/sms/create', [
            'title' => 'Ajouter un Gateway SMS'
        ]);
    }

    /**
     * Store new gateway
     */
    public function storeGateway()
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'provider_code' => $_POST['provider_code'] ?? '',
            'api_url' => $_POST['api_url'] ?? '',
            'api_key' => $_POST['api_key'] ?? '',
            'api_secret' => $_POST['api_secret'] ?? '',
            'sender_id' => $_POST['sender_id'] ?? '',
            'is_active' => isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false,
            'priority' => (int)($_POST['priority'] ?? 0),
            'configuration' => json_encode($_POST['configuration'] ?? [])
        ];

        if (SmsGateway::create($data)) {
            $_SESSION['flash_success'] = 'Gateway SMS ajouté avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'ajout du gateway.';
        }

        redirect('/admin/settings/sms');
    }

    /**
     * Show form to edit gateway
     */
    public function editGateway($id)
    {
        $app = Application::getInstance();
        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash_error'] = 'Gateway introuvable.';
            redirect('/admin/settings/sms');
        }

        echo view('settings/sms/edit', [
            'title' => 'Éditer Gateway SMS',
            'gateway' => $gateway
        ]);
    }

    /**
     * Update gateway
     */
    public function updateGateway($id)
    {
        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash_error'] = 'Gateway introuvable.';
            redirect('/admin/settings/sms');
        }

        $data = [
            'name' => $_POST['name'] ?? $gateway->name,
            'api_url' => $_POST['api_url'] ?? $gateway->api_url,
            'sender_id' => $_POST['sender_id'] ?? $gateway->sender_id,
            'is_active' => isset($_POST['is_active']) ? (bool)$_POST['is_active'] : $gateway->is_active,
            'priority' => (int)($_POST['priority'] ?? $gateway->priority),
        ];

        // Only update API credentials if provided
        if (!empty($_POST['api_key'])) {
            $data['api_key'] = $_POST['api_key'];
        }
        if (!empty($_POST['api_secret'])) {
            $data['api_secret'] = $_POST['api_secret'];
        }

        if (SmsGateway::where('id', $id)->update($data)) {
            $_SESSION['flash_success'] = 'Gateway mis à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        redirect('/admin/settings/sms');
    }

    /**
     * Delete gateway
     */
    public function deleteGateway($id)
    {
        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash_error'] = 'Gateway introuvable.';
            redirect('/admin/settings/sms');
        }

        if ($gateway->delete()) {
            $_SESSION['flash_success'] = 'Gateway supprimé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        redirect('/admin/settings/sms');
    }

    /**
     * Test gateway connection
     */
    public function testGateway($id)
    {
        // Clean any previous output buffer
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            echo json_encode(['success' => false, 'message' => 'Gateway introuvable']);
            exit;
        }

        $result = $gateway->testConnection();
        echo json_encode($result);
        exit;
    }

    /**
     * Set gateway as default
     */
    public function setDefault($id)
    {
        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash_error'] = 'Gateway introuvable.';
            redirect('/admin/settings/sms');
        }

        if ($gateway->setAsDefault()) {
            $_SESSION['flash_success'] = 'Gateway défini par défaut.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        redirect('/admin/settings/sms');
    }

    /**
     * Toggle gateway status
     */
    public function toggleStatus($id)
    {
        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash_error'] = 'Gateway introuvable.';
            redirect('/admin/settings/sms');
        }

        $gateway->is_active = !$gateway->is_active;

        if ($gateway->save()) {
            $_SESSION['flash_success'] = 'Statut mis à jour.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        redirect('/admin/settings/sms');
    }

    /**
     * Switch gateway mode (Mock <-> Production)
     */
    public function switchMode($id)
    {
        // Disable CSRF check for JSON API endpoint
        $_POST['_csrf_disable'] = true;

        // Clean any previous output buffer
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        $gateway = SmsGateway::find($id);

        if (!$gateway) {
            echo json_encode(['success' => false, 'message' => 'Gateway introuvable']);
            exit;
        }

        // Get JSON body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $mode = $data['mode'] ?? 'mock';

        if ($mode === 'production') {
            // Vérifier si des credentials sont déjà présentes
            $hasCredentials = !empty($gateway->api_key) && !empty($gateway->api_secret);

            if (!$hasCredentials) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Impossible de passer en mode PRODUCTION: Aucune credential configurée. Veuillez d\'abord éditer le gateway et entrer vos API Key et API Secret.'
                ]);
                exit;
            }

            // Les credentials existent, le système basculera automatiquement en PROD
            echo json_encode([
                'success' => true,
                'message' => 'Mode PRODUCTION activé. Les SMS seront réellement envoyés et facturés.'
            ]);
        } else {
            // Switch to Mock: Clear credentials
            $pdo = \App\Core\Database\Database::getInstance()->getPdo();
            $stmt = $pdo->prepare("UPDATE sms_gateways SET api_key = NULL, api_secret = NULL WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Mode MOCK (simulation) activé. Les SMS ne seront pas réellement envoyés.'
            ]);
        }

        exit;
    }
}
