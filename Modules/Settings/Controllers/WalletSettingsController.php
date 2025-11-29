<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Models\WalletGateway;
use Modules\Settings\Models\Setting;

class WalletSettingsController
{
    /**
     * Display Wallet settings
     */
    public function index()
    {
        $app = Application::getInstance();

        $gateways = WalletGateway::all();
        $settings = Setting::getByGroup('wallet');

        echo $app->view->render('settings/wallet/index', [
            'title' => 'Configuration Wallet',
            'gateways' => $gateways,
            'settings' => $settings
        ]);
    }

    /**
     * Update wallet settings
     */
    public function update()
    {
        // Update general wallet settings
        Setting::set('wallet.currency', $_POST['currency'] ?? 'XOF', 'string', 'wallet');
        Setting::set('wallet.min_topup', $_POST['min_topup'] ?? 1000, 'integer', 'wallet');

        // Update gateway configurations and statuses
        if (isset($_POST['gateways']) && is_array($_POST['gateways'])) {
            foreach ($_POST['gateways'] as $id => $config) {
                $gateway = WalletGateway::find($id);

                if ($gateway) {
                    $updateData = [
                        'is_active' => isset($config['is_active']) ? (bool)$config['is_active'] : false,
                    ];

                    // Update credentials if provided
                    if (!empty($config['api_key'])) {
                        $updateData['api_key'] = $config['api_key'];
                    }
                    if (!empty($config['api_secret'])) {
                        $updateData['api_secret'] = $config['api_secret'];
                    }
                    if (!empty($config['merchant_id'])) {
                        $updateData['merchant_id'] = $config['merchant_id'];
                    }

                    WalletGateway::where('id', $id)->update($updateData);
                }
            }
        }

        $_SESSION['flash_success'] = 'Configuration Wallet mise à jour avec succès.';
        redirect('/admin/settings/wallet');
    }

    /**
     * Show form to create new gateway
     */
    public function createGateway()
    {
        $app = Application::getInstance();

        echo $app->view->render('settings/wallet/create', [
            'title' => 'Ajouter un Gateway Wallet'
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
            'merchant_id' => $_POST['merchant_id'] ?? null,
            'currency' => $_POST['currency'] ?? 'XOF',
            'transaction_fee' => $_POST['transaction_fee'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        // Handle JSON configuration
        if (!empty($_POST['configuration'])) {
            $data['configuration'] = json_decode($_POST['configuration'], true);
        }

        try {
            $gateway = WalletGateway::create($data);

            // If set as default, update it
            if ($data['is_default']) {
                $gateway->setAsDefault();
            }

            $_SESSION['flash']['success'] = 'Gateway créé avec succès.';
        } catch (\Exception $e) {
            $_SESSION['flash']['error'] = 'Erreur: ' . $e->getMessage();
        }

        redirect('/admin/settings/wallet');
    }

    /**
     * Show form to edit gateway
     */
    public function editGateway($id)
    {
        $app = Application::getInstance();
        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash']['error'] = 'Gateway introuvable.';
            redirect('/admin/settings/wallet');
        }

        echo $app->view->render('settings/wallet/edit', [
            'title' => 'Éditer Gateway Wallet',
            'gateway' => $gateway
        ]);
    }

    /**
     * Update gateway
     */
    public function updateGateway($id)
    {
        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash']['error'] = 'Gateway introuvable.';
            redirect('/admin/settings/wallet');
        }

        $updateData = [
            'name' => $_POST['name'] ?? $gateway->name,
            'api_url' => $_POST['api_url'] ?? $gateway->api_url,
            'currency' => $_POST['currency'] ?? $gateway->currency,
            'transaction_fee' => $_POST['transaction_fee'] ?? $gateway->transaction_fee,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        // Update credentials only if provided
        if (!empty($_POST['api_key'])) {
            $updateData['api_key'] = $_POST['api_key'];
        }
        if (!empty($_POST['api_secret'])) {
            $updateData['api_secret'] = $_POST['api_secret'];
        }
        if (!empty($_POST['merchant_id'])) {
            $updateData['merchant_id'] = $_POST['merchant_id'];
        }

        // Handle JSON configuration
        if (isset($_POST['configuration'])) {
            $updateData['configuration'] = json_decode($_POST['configuration'], true);
        }

        try {
            WalletGateway::where('id', $id)->update($updateData);

            // If set as default, update it
            if ($updateData['is_default']) {
                $gateway->setAsDefault();
            }

            $_SESSION['flash']['success'] = 'Gateway mis à jour avec succès.';
        } catch (\Exception $e) {
            $_SESSION['flash']['error'] = 'Erreur: ' . $e->getMessage();
        }

        redirect('/admin/settings/wallet');
    }

    /**
     * Delete gateway
     */
    public function deleteGateway($id)
    {
        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash']['error'] = 'Gateway introuvable.';
            redirect('/admin/settings/wallet');
        }

        if ($gateway->is_default) {
            $_SESSION['flash']['error'] = 'Impossible de supprimer le gateway par défaut.';
            redirect('/admin/settings/wallet');
        }

        try {
            WalletGateway::where('id', $id)->delete();
            $_SESSION['flash']['success'] = 'Gateway supprimé avec succès.';
        } catch (\Exception $e) {
            $_SESSION['flash']['error'] = 'Erreur: ' . $e->getMessage();
        }

        redirect('/admin/settings/wallet');
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

        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            echo json_encode(['success' => false, 'message' => 'Gateway introuvable']);
            return;
        }

        // Test connection based on provider
        $result = $this->testConnectionByProvider($gateway);
        echo json_encode($result);
    }

    /**
     * Test connection for specific provider
     */
    private function testConnectionByProvider($gateway): array
    {
        try {
            switch ($gateway->provider_code) {
                case 'paydunya':
                    return $this->testPayDunya($gateway);
                case 'cinetpay':
                    return $this->testCinetPay($gateway);
                case 'orange_money':
                case 'wave':
                case 'moov_money':
                case 'mtn_mobile_money':
                    return ['success' => true, 'message' => 'Test non implémenté pour ce provider'];
                default:
                    return ['success' => false, 'message' => 'Provider non reconnu'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Test PayDunya connection
     */
    private function testPayDunya($gateway): array
    {
        $ch = curl_init($gateway->api_url . '/checkout-invoice/confirm/test');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'PAYDUNYA-MASTER-KEY: ' . $gateway->api_key,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 401) {
            return ['success' => true, 'message' => 'API PayDunya accessible'];
        }

        return ['success' => false, 'message' => 'Connexion impossible'];
    }

    /**
     * Test CinetPay connection
     */
    private function testCinetPay($gateway): array
    {
        $ch = curl_init($gateway->api_url . '/payment/check');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $gateway->api_key,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 400 || $httpCode === 401) {
            return ['success' => true, 'message' => 'API CinetPay accessible'];
        }

        return ['success' => false, 'message' => 'Connexion impossible'];
    }

    /**
     * Toggle gateway status
     */
    public function toggleStatus($id)
    {
        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash']['error'] = 'Gateway introuvable.';
            redirect('/admin/settings/wallet');
        }

        $gateway->is_active = !$gateway->is_active;
        $gateway->save();

        $_SESSION['flash']['success'] = 'Statut mis à jour.';
        redirect('/admin/settings/wallet');
    }

    /**
     * Set gateway as default
     */
    public function setDefault($id)
    {
        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash']['error'] = 'Gateway introuvable.';
            redirect('/admin/settings/wallet');
        }

        if ($gateway->setAsDefault()) {
            $_SESSION['flash']['success'] = 'Gateway défini par défaut.';
        } else {
            $_SESSION['flash']['error'] = 'Erreur lors de la mise à jour.';
        }

        redirect('/admin/settings/wallet');
    }
}
