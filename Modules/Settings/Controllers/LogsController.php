<?php

namespace Modules\Settings\Controllers;

class LogsController
{
    public function cronLogs()
    {
        // Vérifier l'authentification
        if (!isset($_SESSION['user'])) {
            redirect('/admin/login');
            exit;
        }

        $logPath = dirname(__DIR__, 3) . '/../../logs/cron.log';
        $lines = 200; // Dernières 200 lignes

        $logs = '';
        if (file_exists($logPath)) {
            $allLines = file($logPath);
            $logs = array_slice($allLines, -$lines);
        }

        echo view('Settings/logs/cron', [
            'title' => 'Logs Cron',
            'logs' => $logs,
            'logPath' => $logPath,
            'exists' => file_exists($logPath)
        ]);
    }

    public function healthLogs()
    {
        // Vérifier l'authentification
        if (!isset($_SESSION['user'])) {
            redirect('/admin/login');
            exit;
        }

        $logPath = dirname(__DIR__, 3) . '/../../logs/health.log';
        $lines = 200;

        $logs = '';
        if (file_exists($logPath)) {
            $allLines = file($logPath);
            $logs = array_slice($allLines, -$lines);
        }

        echo view('Settings/logs/health', [
            'title' => 'Logs Health Check',
            'logs' => $logs,
            'logPath' => $logPath,
            'exists' => file_exists($logPath)
        ]);
    }

    public function smsQueueLogs()
    {
        // Vérifier l'authentification
        if (!isset($_SESSION['user'])) {
            redirect('/admin/login');
            exit;
        }

        $logPath = dirname(__DIR__, 3) . '/storage/logs/sms_cron.log';
        $lines = 200;

        $logs = '';
        if (file_exists($logPath)) {
            $allLines = file($logPath);
            $logs = array_slice($allLines, -$lines);
        }

        echo view('Settings/logs/sms_queue', [
            'title' => 'Logs SMS Queue',
            'logs' => $logs,
            'logPath' => $logPath,
            'exists' => file_exists($logPath)
        ]);
    }

    public function clearLog()
    {
        // Vérifier l'authentification
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }

        $logType = $_POST['log_type'] ?? '';
        $logPaths = [
            'cron' => dirname(__DIR__, 3) . '/../../logs/cron.log',
            'health' => dirname(__DIR__, 3) . '/../../logs/health.log',
            'sms_queue' => dirname(__DIR__, 3) . '/storage/logs/sms_cron.log'
        ];

        if (!isset($logPaths[$logType])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Type de log invalide']);
            exit;
        }

        $logPath = $logPaths[$logType];

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            echo json_encode(['success' => true, 'message' => 'Log vidé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fichier log introuvable']);
        }
    }
}
