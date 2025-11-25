<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\Admin\Services\CronService;

/**
 * Class CronController
 * 
 * Handles Cron tasks management UI in admin backoffice.
 */
class CronController
{
    private CronService $service;
    private Application $app;

    public function __construct()
    {
        $this->service = new CronService();
        $this->app = Application::getInstance();
    }

    /**
     * Cron Tasks List
     */
    public function index()
    {
        $tasks = $this->service->getAllTasks();
        $stats = $this->service->getDashboardStats();

        echo $this->app->view->render('backend/cron/index', [
            'title' => 'Cron Tasks',
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }

    /**
     * Execution Logs
     */
    public function logs()
    {
        $page = (int)($_GET['page'] ?? 1);
        $taskClass = $_GET['task'] ?? null;

        $data = $this->service->getExecutionLogs($page, 20, $taskClass);

        echo $this->app->view->render('backend/cron/logs', [
            'title' => 'Cron Execution Logs',
            'logs' => $data['logs'],
            'pagination' => $data,
            'filter_task' => $taskClass,
        ]);
    }

    /**
     * Statistics Page
     */
    public function stats()
    {
        $taskStats = $this->service->getTaskStatistics();

        echo $this->app->view->render('backend/cron/stats', [
            'title' => 'Cron Statistics',
            'task_stats' => $taskStats,
        ]);
    }

    /**
     * Toggle Task Enabled/Disabled
     */
    public function toggle()
    {
        $taskClass = $_POST['task_class'] ?? '';

        if ($taskClass) {
            $enabled = $this->service->toggleTask($taskClass);

            $status = $enabled ? 'enabled' : 'disabled';
            $_SESSION['success'] = "Task has been {$status} successfully.";
        }

        header('Location: /admin/cron');
        exit;
    }

    /**
     * Run Task Manually
     */
    public function runManually()
    {
        $taskClass = $_POST['task_class'] ?? '';

        if ($taskClass) {
            $result = $this->service->runTaskManually($taskClass);

            if ($result['success']) {
                $_SESSION['success'] = "Task executed successfully in {$result['duration']}s";
            } else {
                $_SESSION['error'] = "Task failed: " . $result['error'];
            }
        }

        header('Location: /admin/cron');
        exit;
    }
}
