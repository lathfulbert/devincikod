<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class WalletController
{
    /**
     * Show top-up page
     */
    public function topup()
    {
        $app = Application::getInstance();

        // Mock wallet balance for now (should be fetched from DB)
        $balance = 500.00;

        echo view('SmsCore/wallet/topup', [
            'title' => 'Recharger le compte',
            'balance' => $balance
        ]);
    }

    /**
     * Process top-up request
     */
    public function processTopup()
    {
        // TODO: Implement payment gateway integration
        $amount = $_POST['amount'] ?? 0;

        if ($amount < 5) {
            $_SESSION['flash_error'] = 'Le montant minimum est de $5.00';
            redirect('/admin/wallet/topup');
            exit;
        }

        // Mock success
        $_SESSION['flash_success'] = "Recharge de $$amount initiée avec succès (Simulation)";
        redirect('/admin/wallet/topup');
    }
}
