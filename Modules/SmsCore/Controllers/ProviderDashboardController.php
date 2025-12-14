<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use App\Core\Http\Request;
use Modules\Settings\Models\SmsGateway;

class ProviderDashboardController
{
    /**
     * Show list of providers and their status
     * Route: /admin/sms/providers
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('sms.providers.manage')) {
            $_SESSION['flash_error'] = "Vous n'avez pas la permission d'accéder à cette page.";
            redirect('/admin/dashboard');
            exit;
        }
        $gateways = SmsGateway::where('is_active', true)->get();

        echo view('SmsCore/providers/index', [
            'title' => 'Tableau de bord Fournisseurs',
            'gateways' => $gateways
        ]);
    }

    /**
     * Show Orange SMS Contracts & Statistics
     * Route: /admin/sms/providers/orange
     */
    public function orange()
    {
        $gatewayConfig = SmsGateway::where('provider_code', 'orange_ci')->first();

        if (!$gatewayConfig || !$gatewayConfig->is_active) {
            $_SESSION['flash_error'] = 'Le gateway Orange CI n\'est pas configuré ou inactif.';
            redirect('/admin/sms/providers');
            exit;
        }

        $gateway = new \Modules\SmsCore\Gateways\OrangeCIGateway($gatewayConfig);

        // Fetch Contracts
        $contractsResult = $gateway->getContracts();
        $contracts = $contractsResult['success'] ? ($contractsResult['data'] ?? []) : [];
        $contractsError = $contractsResult['success'] ? null : $contractsResult['message'];

        // Fetch Statistics
        $statsResult = $gateway->getStatistics();
        $statistics = $statsResult['success'] ? ($statsResult['data'] ?? []) : [];
        $statsError = $statsResult['success'] ? null : $statsResult['message'];

        // Fetch Purchase Orders
        $ordersResult = $gateway->getPurchaseOrders();
        $orders = $ordersResult['success'] ? ($ordersResult['data'] ?? []) : [];
        $ordersError = $ordersResult['success'] ? null : $ordersResult['message'];

        echo view('SmsCore/providers/orange', [
            'title' => 'Statistiques Orange SMS',
            'gateway' => $gatewayConfig,
            'contracts' => $contracts,
            'contracts_error' => $contractsError,
            'statistics' => $statistics,
            'stats_error' => $statsError,
            'orders' => $orders,
            'orders_error' => $ordersError
        ]);
    }
}
