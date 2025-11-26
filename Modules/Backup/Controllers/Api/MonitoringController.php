<?php

namespace Modules\Backup\Controllers\Api;

use App\Core\Http\Controller;
use App\Core\Http\Response;
use Modules\Backup\Services\MonitoringService;

class MonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct()
    {
        $this->monitoringService = new MonitoringService();
    }

    public function health()
    {
        $health = $this->monitoringService->getSystemHealth();
        return Response::json(['status' => 'success', 'data' => $health]);
    }

    public function stats()
    {
        // Same as health for now, or could be historical stats
        $health = $this->monitoringService->getSystemHealth();
        return Response::json(['status' => 'success', 'data' => $health]);
    }
}
