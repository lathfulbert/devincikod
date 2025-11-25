<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\Admin\Services\QueueService;

/**
 * Class QueueController
 * 
 * Handles Queue management UI in admin backoffice.
 */
class QueueController
{
    private QueueService $service;
    private Application $app;

    public function __construct()
    {
        $this->service = new QueueService();
        $this->app = Application::getInstance();
    }

    /**
     * Queue Dashboard - Overview
     */
    public function index()
    {
        $stats = $this->service->getDashboardStats();
        $recentActivity = $this->service->getRecentActivity(10);

        echo $this->app->view->render('backend/queue/index', [
            'title' => 'Queue Management',
            'stats' => $stats,
            'activity' => $recentActivity,
        ]);
    }

    /**
     * Active Jobs List
     */
    public function jobs()
    {
        $page = (int)($_GET['page'] ?? 1);
        $data = $this->service->getActiveJobs($page, 20);

        echo $this->app->view->render('backend/queue/jobs', [
            'title' => 'Active Jobs',
            'jobs' => $data['jobs'],
            'pagination' => $data,
        ]);
    }

    /**
     * Failed Jobs List
     */
    public function failed()
    {
        $page = (int)($_GET['page'] ?? 1);
        $data = $this->service->getFailedJobs($page, 20);

        echo $this->app->view->render('backend/queue/failed', [
            'title' => 'Failed Jobs',
            'jobs' => $data['jobs'],
            'pagination' => $data,
        ]);
    }

    /**
     * Retry Failed Job
     */
    public function retry()
    {
        $id = (int)($_POST['id'] ?? 0);

        if ($id) {
            $success = $this->service->retryFailedJob($id);

            if ($success) {
                $_SESSION['success'] = "Job #{$id} has been retried successfully.";
            } else {
                $_SESSION['error'] = "Failed to retry job #{$id}.";
            }
        }

        redirect('/admin/queue/failed');
    }

    /**
     * Retry All Failed Jobs
     */
    public function retryAll()
    {
        $count = $this->service->retryAllFailedJobs();
        $_SESSION['success'] = "{$count} job(s) retried successfully.";

        redirect('/admin/queue/failed');
    }

    /**
     * Delete Failed Job
     */
    public function delete()
    {
        $id = (int)($_POST['id'] ?? 0);

        if ($id) {
            $success = $this->service->deleteFailedJob($id);

            if ($success) {
                $_SESSION['success'] = "Job #{$id} has been deleted.";
            } else {
                $_SESSION['error'] = "Failed to delete job #{$id}.";
            }
        }

        redirect('/admin/queue/failed');
    }

    /**
     * Queue Statistics Page
     */
    public function stats()
    {
        $period = $_GET['period'] ?? '24h';
        $statistics = $this->service->getStatistics($period);

        echo $this->app->view->render('backend/queue/stats', [
            'title' => 'Queue Statistics',
            'stats' => $statistics,
            'period' => $period,
        ]);
    }

    /**
     * API Endpoint - Get Stats (JSON)
     */
    public function apiStats()
    {
        header('Content-Type: application/json');

        $stats = $this->service->getDashboardStats();
        echo json_encode($stats);
        exit;
    }
}
