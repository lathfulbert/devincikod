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
                    'default' => 10,
                    'networks' => [
                        'orange' => 12,
                        'mtn' => 10,
                        'moov' => 10
                    ]
                ]
            ];
        }

        echo $app->view->render('backend/sms/pricing/index', [
            'pricingGrid' => json_encode($pricingGrid, JSON_PRETTY_PRINT)
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
     * Display billing logs
     */
    public function logs()
    {
        $app = Application::getInstance();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = SmsBillingLog::with('user')->orderBy('created_at', 'desc');

        // Filters
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $query->where('status', $_GET['status']);
        }

        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            $query->where('user_id', $_GET['user_id']);
        }

        $total = $query->count();
        $logs = $query->limit($limit)->offset($offset)->get();
        $totalPages = ceil($total / $limit);

        echo $app->view->render('backend/sms/billing/index', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $total
        ]);
    }
}
