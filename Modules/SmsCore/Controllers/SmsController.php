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
                    // Initialize services for billing
                    $pricingService = new \Modules\SmsCore\Services\SmsPricingService();
                    $billingService = new \Modules\SmsCore\Services\SmsBillingService();
                    $senderService = new \Modules\SmsCore\Services\SmsSenderService($gateway, $pricingService, $billingService);

                    // Send with billing logic
                    $result = $senderService->send($to, $message, $sender, [
                        'user_id' => $_SESSION['user']['id'] ?? null,
                        'gateway_name' => $gatewayConfig->provider_code
                    ]);

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
            try {
                $campaignName = $_POST['campaign_name'] ?? 'Untitled Campaign';
                $message = $_POST['message'] ?? '';
                $sender = $_POST['sender'] ?? 'SMS';
                $source = $_POST['source'] ?? 'manual';
                $scheduledAt = $_POST['scheduled_at'] ?? null;

                // Parse recipients based on source
                $recipients = [];

                if ($source === 'manual') {
                    $manual = $_POST['recipients_manual'] ?? '';
                    $recipients = array_map('trim', explode(',', $manual));
                } elseif ($source === 'csv' && isset($_FILES['recipients_file'])) {
                    $recipients = $this->parseCSV($_FILES['recipients_file']);
                }

                // Remove empty values
                $recipients = array_filter($recipients);

                if (empty($recipients)) {
                    $_SESSION['flash_error'] = 'Aucun destinataire valide trouvé.';
                    redirect('/admin/sms/bulk');
                    exit;
                }

                // Create campaign
                $campaign = \Modules\SmsCore\Models\SmsCampaign::create([
                    'name' => $campaignName,
                    'message' => $message,
                    'sender_id' => $sender,
                    'status' => $scheduledAt ? 'scheduled' : 'draft',
                    'total_recipients' => count($recipients),
                    'sent_count' => 0,
                    'failed_count' => 0,
                    'scheduled_at' => $scheduledAt ? date('Y-m-d H:i:s', strtotime($scheduledAt)) : null,
                    'created_by' => $_SESSION['user']['id'] ?? null
                ]);

                // Queue all recipients
                $queueManager = \App\Core\Queue\QueueManager::getInstance();

                foreach ($recipients as $recipient) {
                    // Add to sms_queue table
                    $queueItem = \Modules\SmsCore\Models\SmsQueue::create([
                        'campaign_id' => $campaign->id,
                        'recipient' => $recipient,
                        'message' => $message,
                        'sender_id' => $sender,
                        'status' => 'pending',
                        'scheduled_at' => $scheduledAt ? date('Y-m-d H:i:s', strtotime($scheduledAt)) : null
                    ]);

                    // Push to Core Queue system
                    $queueManager->push(
                        \Modules\SmsCore\Jobs\SendBulkSmsJob::class,
                        ['queueId' => $queueItem->id],
                        'sms'
                    );
                }

                // Mark campaign as sending if not scheduled
                if (!$scheduledAt) {
                    $campaign->markAsStarted();
                }

                $_SESSION['flash_success'] = "Campagne créée avec succès! {$campaign->total_recipients} SMS en file d'attente.";
                redirect('/admin/sms/campaigns');
                exit;

            } catch (\Exception $e) {
                $_SESSION['flash_error'] = 'Erreur lors de la création de la campagne: ' . $e->getMessage();
                redirect('/admin/sms/bulk');
                exit;
            }
        }

        echo $app->view->render('backend/sms/bulk', [
            'title' => 'Send Bulk SMS'
        ]);
    }

    /**
     * Parse CSV file to extract phone numbers
     */
    private function parseCSV($file): array
    {
        $recipients = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $recipients;
        }

        $handle = fopen($file['tmp_name'], 'r');

        if ($handle) {
            while (($data = fgetcsv($handle)) !== false) {
                if (!empty($data[0])) {
                    $recipients[] = trim($data[0]);
                }
            }
            fclose($handle);
        }

        return $recipients;
    }
}
