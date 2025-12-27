<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\Settings\Models\Setting;
use Modules\SmsCore\Models\SmsBillingLog;

class SmsPricingController
{
    /**
     * Display pricing grid configuration
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('sms.pricing.manage')) {
            $_SESSION['flash_error'] = "Vous n'avez pas la permission d'accéder à cette page.";
            redirect('/admin/dashboard');
            exit;
        }
        $app = Application::getInstance();
        $pricingGrid = Setting::get('sms_pricing_grid', []);

        // Default structure if empty
        if (empty($pricingGrid)) {
            $pricingGrid = [
                'default' => [
                    'price' => 15,
                    'currency' => 'XOF'
                ],
                'CI' => [
                    'name' => 'Côte d\'Ivoire',
                    'default' => 10,
                    'networks' => [
                        'orange' => 12,
                        'mtn' => 10,
                        'moov' => 10
                    ]
                ]
            ];
        }

        // Extract default price and countries
        $defaultPrice = $pricingGrid['default']['price'] ?? 15;
        $countries = [];

        foreach ($pricingGrid as $code => $data) {
            if ($code !== 'default' && is_array($data)) {
                $countries[$code] = $data;
            }
        }

        return view('SmsCore/sms/pricing/index', [
            'defaultPrice' => $defaultPrice,
            'countries' => $countries
        ]);
    }

    /**
     * Update pricing grid
     */
    public function update()
    {
        $json = $_POST['pricing_json'] ?? '';

        // Validate JSON
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $_SESSION['flash_error'] = 'Invalid JSON format: ' . json_last_error_msg();
            redirect('/admin/sms/pricing');
            exit;
        }

        Setting::set('sms_pricing_grid', $data, 'json', 'sms_pricing');

        $_SESSION['flash_success'] = 'Pricing grid updated successfully';
        redirect('/admin/sms/pricing');
        exit;
    }

    /**
     * Update default price
     */
    public function updateDefault()
    {
        $defaultPrice = (float)($_POST['default_price'] ?? 15);

        $pricingGrid = Setting::get('sms_pricing_grid', []);
        $pricingGrid['default'] = [
            'price' => $defaultPrice,
            'currency' => 'XOF'
        ];

        Setting::set('sms_pricing_grid', $pricingGrid, 'json', 'sms_pricing');

        $_SESSION['flash_success'] = 'Tarif par défaut mis à jour';
        redirect('/admin/sms/pricing');
        exit;
    }

    /**
     * Update country pricing
     */
    public function updateCountry()
    {
        $countryCode = $_POST['country_code'] ?? $_POST['country'] ?? '';
        $country = $_POST['country'] ?? '';
        $defaultPrice = (float)($_POST['default_price'] ?? 0);
        $operators = $_POST['operators'] ?? [];

        if (empty($country)) {
            $_SESSION['flash_error'] = 'Pays requis';
            redirect('/admin/sms/pricing');
            exit;
        }

        $pricingGrid = Setting::get('sms_pricing_grid', []);

        // Get country name
        $countryNames = [
            'CI' => 'Côte d\'Ivoire',
            'SN' => 'Sénégal',
            'ML' => 'Mali',
            'BF' => 'Burkina Faso',
            'BJ' => 'Bénin',
            'TG' => 'Togo',
            'NE' => 'Niger',
            'GN' => 'Guinée',
            'CM' => 'Cameroun',
            'FR' => 'France',
            'US' => 'États-Unis'
        ];

        $pricingGrid[$country] = [
            'name' => $countryNames[$country] ?? $country,
            'default' => $defaultPrice
        ];

        // Add operators
        $networks = [];
        foreach ($operators as $operator) {
            if (!empty($operator['name']) && !empty($operator['price'])) {
                $networks[strtolower($operator['name'])] = (float)$operator['price'];
            }
        }

        if (!empty($networks)) {
            $pricingGrid[$country]['networks'] = $networks;
        }

        Setting::set('sms_pricing_grid', $pricingGrid, 'json', 'sms_pricing');

        $_SESSION['flash_success'] = 'Tarif pays mis à jour';
        redirect('/admin/sms/pricing');
        exit;
    }

    /**
     * Delete country pricing
     */
    public function deleteCountry()
    {
        $countryCode = $_POST['country_code'] ?? '';

        if (empty($countryCode)) {
            $_SESSION['flash_error'] = 'Code pays requis';
            redirect('/admin/sms/pricing');
            exit;
        }

        $pricingGrid = Setting::get('sms_pricing_grid', []);
        unset($pricingGrid[$countryCode]);

        Setting::set('sms_pricing_grid', $pricingGrid, 'json', 'sms_pricing');

        $_SESSION['flash_success'] = 'Tarif pays supprimé';
        redirect('/admin/sms/pricing');
        exit;
    }

    /**
     * Display billing logs
     */
    public function logs()
    {
        $user = auth()->user();
        if (!$user->can('sms.billing.manage')) {
            $_SESSION['flash_error'] = "Vous n'avez pas la permission d'accéder à cette page.";
            redirect('/admin/dashboard');
            exit;
        }
        $app = Application::getInstance();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = SmsBillingLog::orderBy('created_at', 'desc');

        // Filters
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $query->where('status', $_GET['status']);
        }

        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            $query->where('user_id', $_GET['user_id']);
        }

        $total = $query->count();
        $logs = $query->with('user')->limit($limit)->offset($offset)->get();
        $totalPages = ceil($total / $limit);

        return view('SmsCore/sms/billing/index', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $total
        ]);
    }
}
