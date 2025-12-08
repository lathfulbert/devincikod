<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\Settings\Models\SmsGateway;
use Modules\SmsCore\Models\SmsMessage;
use Modules\SmsCore\Models\SenderName;
use Modules\SmsCore\Services\SmsGatewayFactory;
use Modules\SmsCore\Services\PhoneNumberService;
use Modules\SmsCore\Services\FileImportService;
use Modules\SmsCore\Services\SmsQueueService;

class SmsController
{
    public function send()
    {
        $app = Application::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process SMS sending
            $to = $_POST['to'] ?? '';
            $message = $_POST['message'] ?? '';
            $senderNameId = (int)($_POST['sender_name_id'] ?? 0);
            $gatewayCode = $_POST['gateway'] ?? 'auto';
            $userId = $_SESSION['user']['id'] ?? null;

            // Format phone number with country code prefix
            $to = PhoneNumberService::format($to);

            // Validate phone number
            if (!PhoneNumberService::validate($to)) {
                $_SESSION['flash_error'] = 'Numéro de téléphone invalide: ' . htmlspecialchars($to);
                redirect('/admin/sms/send');
                exit;
            }

            // Get sender name
            $senderName = null;
            $sender = 'SMS';

            if ($senderNameId) {
                // Verify user has access to this sender name
                if (!SenderName::userHasAccess($userId, $senderNameId)) {
                    $_SESSION['flash_error'] = 'Vous n\'avez pas accès à ce Sender Name';
                    redirect('/admin/sms/send');
                    exit;
                }

                $senderName = SenderName::find($senderNameId);
                if ($senderName) {
                    $sender = $senderName->name;
                }
            }

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

        // Get user's sender names
        $userId = $_SESSION['user']['id'] ?? null;
        $senderNames = $userId ? SenderName::getForUser($userId) : [];

        echo view('SmsCore/sms/send', [
            'title' => 'Send SMS',
            'gateways' => $gateways,
            'senderNames' => $senderNames
        ]);
    }

    public function history()
    {
        $app = Application::getInstance();

        // Get real SMS history from database
        $messages = SmsMessage::orderBy('created_at', 'DESC')->get();

        echo view('SmsCore/sms/history', [
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

        echo view('SmsCore/sms/details', [
            'sms' => $sms,
            'title' => 'SMS Details'
        ]);
    }

    /**
     * Redirect old bulk route to new tabbed send interface
     * Routes: GET/POST /admin/sms/bulk
     */
    public function bulk()
    {
        // Redirect to the new tabbed interface
        redirect('/admin/sms/send');
        exit;
    }

    /**
     * Handle bulk SMS sending via POST from the tabbed interface
     * Routes: /admin/sms/send-bulk
     */
    public function sendBulk()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/sms/send');
            exit;
        }

        try {
            $sendType = $_POST['send_type'] ?? 'manual';
            $message = $_POST['message'] ?? '';
            $senderNameId = (int)($_POST['sender_name_id'] ?? 0);
            $campaignName = $_POST['campaign_name'] ?? 'Campagne ' . date('d/m/Y H:i');
            $scheduledAt = $_POST['scheduled_at'] ?? null;
            $userId = $_SESSION['user']['id'] ?? null;

            // Parse scheduled_at to proper datetime format
            $scheduledAtFormatted = null;
            if (!empty($scheduledAt)) {
                $scheduledAtFormatted = date('Y-m-d H:i:s', strtotime($scheduledAt));
            }

            // Validate message
            if (empty($message)) {
                $_SESSION['flash_error'] = 'Le message ne peut pas être vide.';
                redirect('/admin/sms/send');
                exit;
            }

            // Get sender name
            $senderName = null;
            $sender = 'SMS';

            if ($senderNameId) {
                // Verify user has access to this sender name
                if (!SenderName::userHasAccess($userId, $senderNameId)) {
                    $_SESSION['flash_error'] = 'Vous n\'avez pas accès à ce Sender Name';
                    redirect('/admin/sms/send');
                    exit;
                }

                $senderName = SenderName::find($senderNameId);
                if ($senderName) {
                    $sender = $senderName->name;
                }
            }

            // Parse recipients based on send type
            $recipients = [];

            switch ($sendType) {
                case 'manual':
                    $recipients = $this->parseManualRecipients($_POST['recipients_manual'] ?? '');
                    break;

                case 'file':
                    if (!isset($_FILES['recipients_file'])) {
                        throw new \Exception('Aucun fichier fourni');
                    }
                    $recipients = FileImportService::import($_FILES['recipients_file']);
                    break;

                case 'contacts':
                    $recipients = $this->parseContactRecipients($_POST['contact_ids'] ?? []);
                    break;

                default:
                    throw new \Exception('Type d\'envoi invalide');
            }

            // Format all phone numbers
            $recipients = PhoneNumberService::formatMultiple($recipients);

            // Remove duplicates
            $recipients = PhoneNumberService::removeDuplicates($recipients);

            if (empty($recipients)) {
                $_SESSION['flash_error'] = 'Aucun destinataire valide trouvé.';
                redirect('/admin/sms/send');
                exit;
            }

            $recipientCount = count($recipients);

            // Check if we should use queue based on threshold
            $useQueue = SmsQueueService::shouldUseQueue($recipientCount);

            // Create campaign
            $campaign = \Modules\SmsCore\Models\SmsCampaign::create([
                'name' => $campaignName,
                'message' => $message,
                'sender_id' => $sender,
                'status' => $scheduledAtFormatted ? 'scheduled' : ($useQueue ? 'draft' : 'sending'),
                'total_recipients' => $recipientCount,
                'sent_count' => 0,
                'failed_count' => 0,
                'scheduled_at' => $scheduledAtFormatted,
                'created_by' => $userId
            ]);

            // Decide: Direct send or Queue
            if (!$useQueue && !$scheduledAtFormatted) {
                // DIRECT SEND (< threshold, e.g. < 100)
                $this->sendDirectBulk($recipients, $message, $sender, $campaign, $userId);
                $successMessage = "Envoi en cours! {$campaign->sent_count}/{$recipientCount} SMS envoyés avec succès.";
                $_SESSION['flash_success'] = $successMessage;
                redirect('/admin/sms/campaigns');
            } else {
                // USE QUEUE (>= threshold or scheduled)
                $this->sendViaQueue($recipients, $message, $sender, $campaign, $scheduledAtFormatted);

                if ($scheduledAtFormatted) {
                    $successMessage = "Campagne programmée! {$recipientCount} SMS seront envoyés le " . date('d/m/Y à H:i', strtotime($scheduledAtFormatted));
                } else {
                    $successMessage = "Campagne en queue! {$recipientCount} SMS seront traités automatiquement.";
                }

                $_SESSION['flash_success'] = $successMessage;
                redirect('/admin/sms/campaigns');
            }

            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la création de la campagne: ' . $e->getMessage();
            redirect('/admin/sms/send');
            exit;
        }
    }

    /**
     * Parse manually entered recipients (textarea)
     * 
     * @param string $text Raw text with phone numbers
     * @return array Array of phone numbers
     */
    private function parseManualRecipients(string $text): array
    {
        return PhoneNumberService::parseMultiple($text);
    }

    /**
     * Parse recipients from selected contacts
     * 
     * @param array $contactIds Array of contact IDs
     * @return array Array of phone numbers
     */
    private function parseContactRecipients(array $contactIds): array
    {
        $recipients = [];

        foreach ($contactIds as $contactId) {
            $contact = \Modules\Contacts\Models\Contact::find((int)$contactId);
            if ($contact && !empty($contact->phone)) {
                $recipients[] = $contact->phone;
            }
        }

        return $recipients;
    }

    /**
     * Download CSV template for file import
     */
    public function downloadTemplate()
    {
        $csv = FileImportService::generateTemplate();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sms_import_template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv;
        exit;
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

    /**
     * Send SMS directly (synchronous) - for small batches
     *
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message
     * @param string $sender Sender ID
     * @param object $campaign Campaign object
     * @param int|null $userId User ID
     */
    private function sendDirectBulk(array $recipients, string $message, string $sender, $campaign, $userId)
    {
        $gateway = SmsGateway::getDefault();

        if (!$gateway) {
            throw new \Exception('Aucun gateway SMS configuré');
        }

        $gatewayInstance = SmsGatewayFactory::create($gateway);

        if (!$gatewayInstance) {
            throw new \Exception('Gateway non implémenté');
        }

        // Initialize services
        $pricingService = new \Modules\SmsCore\Services\SmsPricingService();
        $billingService = new \Modules\SmsCore\Services\SmsBillingService();
        $senderService = new \Modules\SmsCore\Services\SmsSenderService(
            $gatewayInstance,
            $pricingService,
            $billingService
        );

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                $result = $senderService->send($recipient, $message, $sender, [
                    'user_id' => $userId,
                    'gateway_name' => $gateway->provider_code,
                    'campaign_id' => $campaign->id
                ]);

                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                error_log("Direct send failed for $recipient: " . $e->getMessage());
            }
        }

        // Update campaign stats
        $campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => 'completed'
        ]);
    }

    /**
     * Send SMS via queue (asynchronous) - for large batches
     *
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message
     * @param string $sender Sender ID
     * @param object $campaign Campaign object
     * @param string|null $scheduledAt Scheduled time
     */
    private function sendViaQueue(array $recipients, string $message, string $sender, $campaign, $scheduledAt = null)
    {
        $added = SmsQueueService::addToQueue($recipients, $message, $sender, [
            'campaign_id' => $campaign->id,
            'user_id' => $_SESSION['user']['id'] ?? null,
            'scheduled_at' => $scheduledAt
        ]);

        // Mark campaign as queued
        $campaign->update([
            'status' => $scheduledAt ? 'scheduled' : 'queued'
        ]);

        return $added;
    }

    /**
     * Show Orange SMS Contracts/Balance
     * Route: /admin/sms/contracts
     */
    public function contracts()
    {
        $app = Application::getInstance();

        // Find Orange Gateway config
        $gatewayConfig = SmsGateway::where('provider_code', 'orange_ci')->first();

        if (!$gatewayConfig || !$gatewayConfig->is_active) {
            $_SESSION['flash_error'] = 'Le gateway Orange CI n\'est pas configuré ou inactif.';
            redirect('/admin/sms/dashboard');
            exit;
        }

        // Create gateway instance directly to access specific method
        $gateway = new \Modules\SmsCore\Gateways\OrangeCIGateway($gatewayConfig);

        $result = $gateway->getContracts();

        $contracts = [];
        $error = null;

        if ($result['success']) {
            $contracts = $result['data'] ?? [];
        } else {
            $error = $result['message'] . (isset($result['error']) ? ': ' . $result['error'] : '');
        }

        echo view('SmsCore/sms/contracts', [
            'title' => 'Contrats Orange SMS',
            'contracts' => $contracts,
            'error' => $error,
            'gateway' => $gatewayConfig
        ]);
    }
}
