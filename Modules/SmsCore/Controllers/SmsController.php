<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class SmsController
{
    public function send()
    {
        $app = Application::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process SMS sending
            $to = $_POST['to'] ?? '';
            $message = $_POST['message'] ?? '';
            $sender = $_POST['sender'] ?? 'SMS';

            // TODO: Implement actual SMS sending with services

            redirect('/admin/sms/history');
            exit;
        }

        echo $app->view->render('backend/sms/send', [
            'title' => 'Send SMS'
        ]);
    }

    public function history()
    {
        $app = Application::getInstance();

        // Mock SMS history
        $messages = [
            [
                'id' => 1,
                'to' => '+1234567890',
                'message' => 'Hello World',
                'status' => 'delivered',
                'gateway' => 'Infobip',
                'cost' => 0.03,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'id' => 2,
                'to' => '+0987654321',
                'message' => 'Test message',
                'status' => 'sent',
                'gateway' => 'OrangeSMS',
                'cost' => 0.04,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ]
        ];

        echo $app->view->render('backend/sms/history', [
            'messages' => $messages,
            'title' => 'SMS History'
        ]);
    }

    public function bulk()
    {
        $app = Application::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process bulk SMS
            $recipients = $_POST['recipients'] ?? []; // Array or CSV
            $message = $_POST['message'] ?? '';

            // TODO: Queue bulk messages

            redirect('/admin/sms/history');
            exit;
        }

        echo $app->view->render('backend/sms/bulk', [
            'title' => 'Send Bulk SMS'
        ]);
    }
}
