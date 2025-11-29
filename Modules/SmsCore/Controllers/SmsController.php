<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\Settings\Models\SmsGateway;
use Modules\SmsCore\Models\SmsMessage;
use Modules\SmsCore\Services\SmsGatewayFactory;

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
            $gatewayCode = $_POST['gateway'] ?? 'auto';

            try {
                // Get gateway config
                $gatewayConfig = null;
                if ($gatewayCode === 'auto') {
                    $gatewayConfig = SmsGateway::getDefault();
                } else {
                    $gatewayConfig = SmsGateway::where('provider_code', $gatewayCode)
                        ->where('is_active', 1)
                        ->first();
                }

                if (!$gatewayConfig) {
                    $_SESSION['flash_error'] = 'Aucun gateway SMS disponible. Veuillez configurer un gateway.';
                    redirect('/admin/sms/send');
                    exit;
                }

                // Create message record
                $smsMessage = SmsMessage::create([
                    'user_id' => $_SESSION['user']['id'] ?? null,
                    'to' => $to,
                    'from' => $sender,
                    'message' => $message,
                    'gateway' => $gatewayConfig->provider_code,
                    'status' => 'pending',
                    'message_id' => 'SMS-' . uniqid(),
                ]);

                // Create gateway instance and send SMS
                $gateway = SmsGatewayFactory::create($gatewayConfig);

                if ($gateway) {
                    $result = $gateway->send($to, $message, $sender);

                    // Update message with gateway response
                    $smsMessage->gateway_response = $result['gateway_response'] ?? null;

                    if ($result['success']) {
                        $smsMessage->markAsSent($result['gateway_message_id'] ?? '');
                        $_SESSION['flash_success'] = 'SMS envoyé avec succès via ' . $gatewayConfig->name . ' - ID: ' . ($result['gateway_message_id'] ?? 'N/A');
                    } else {
                        $smsMessage->markAsFailed($result['message'] ?? 'Unknown error');
                        $_SESSION['flash_error'] = 'Échec d\'envoi: ' . ($result['message'] ?? 'Erreur inconnue');
                    }
                } else {
                    $smsMessage->markAsFailed('Gateway not implemented');
                    $_SESSION['flash_error'] = 'Gateway non implémenté pour ' . $gatewayConfig->provider_code;
                }

            } catch (\Exception $e) {
                if (isset($smsMessage)) {
                    $smsMessage->markAsFailed($e->getMessage());
                }
                $_SESSION['flash_error'] = 'Erreur lors de l\'envoi: ' . $e->getMessage();
            }

            redirect('/admin/sms/history');
            exit;
        }

        // Get available gateways for dropdown
        $gateways = SmsGateway::where('is_active', 1)->get();

        echo $app->view->render('backend/sms/send', [
            'title' => 'Send SMS',
            'gateways' => $gateways
        ]);
    }

    public function history()
    {
        $app = Application::getInstance();

        // Get real SMS history from database
        $messages = SmsMessage::orderBy('created_at', 'DESC')->get();

        echo $app->view->render('backend/sms/history', [
            'messages' => $messages,
            'title' => 'SMS History'
        ]);
    }

    public function details($id)
    {
        $app = Application::getInstance();

        $sms = SmsMessage::find($id);

        if (!$sms) {
            $_SESSION['flash_error'] = 'SMS introuvable.';
            redirect('/admin/sms/history');
            exit;
        }

        echo $app->view->render('backend/sms/details', [
            'sms' => $sms,
            'title' => 'SMS Details'
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
