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
     * Set gateway as default
     */
    public function setDefault($id)
    {
        $gateway = WalletGateway::find($id);

        if (!$gateway) {
            $_SESSION['flash_error'] = 'Gateway introuvable.';
            redirect('/admin/settings/wallet');
        }

        if ($gateway->setAsDefault()) {
            $_SESSION['flash_success'] = 'Gateway défini par défaut.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        redirect('/admin/settings/wallet');
    }
}
