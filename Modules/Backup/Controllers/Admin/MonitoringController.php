<?php

namespace Modules\Backup\Controllers\Admin;

use App\Core\Application;
use Modules\Backup\Services\MonitoringService;

class MonitoringController
{
    protected MonitoringService $monitoringService;

    public function __construct()
    {
        $this->monitoringService = new MonitoringService();
    }

    public function index()
    {
        $app = Application::getInstance();
        $health = $this->monitoringService->getSystemHealth();

        echo $app->view->render('backup/admin/health', [
            'title' => 'Santé du système',
            'health' => $health
        ]);
    }

    public function stats()
    {
        $health = $this->monitoringService->getSystemHealth();

        header('Content-Type: application/json');
        return json_encode($health);
        exit;
    }
}
