<?php

namespace Modules\Backup\Controllers\Admin;

use App\Core\Http\Controller;
use Modules\Backup\Services\MonitoringService;

class MonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct()
    {
        $this->monitoringService = new MonitoringService();
    }

    public function index()
    {
        $health = $this->monitoringService->getSystemHealth();
        return $this->view('Backup::admin.health', ['health' => $health]);
    }

    public function stats()
    {
        // Return JSON for charts if needed, or render a stats view
        $health = $this->monitoringService->getSystemHealth();
        return Response::json($health);
    }
}
