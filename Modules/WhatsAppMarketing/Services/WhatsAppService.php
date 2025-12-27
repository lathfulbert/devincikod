<?php

namespace Modules\WhatsAppMarketing\Services;

use Modules\WhatsAppMarketing\Models\WhatsAppGateway;
use Modules\WhatsAppMarketing\Models\WhatsAppMessage;
use Modules\WhatsAppMarketing\Services\WhatsAppGatewayFactory;

class WhatsAppService
{
    /**
     * Send a template message
     */
    public function sendTemplate(string $to, string $templateName, string $languageCode, array $components, ?int $campaignId = null): array
    {
        try {
            // Get user ID from session/context - Assuming logged in user or admin
            // For now, we'll use the campaign's creator or fallback to current user.
            // If called from a campaign context, we might need to pass user_id.
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                // If running from CLI or Cron, we need another way to identify user.
                // For now, fail if no user logic.
                // throw new \Exception("User ID required for billing.");
            }

            // Estimate cost (should come from gateway/pricing service)
            $estimatedCost = 0.05; // Fixed cost for now

            $walletService = new \Modules\Wallet\Services\WalletService();
            if ($userId && !$walletService->hasBalance($userId, $estimatedCost)) {
                return ['success' => false, 'error' => 'Insufficient funds'];
            }

            // Get default gateway
            $gateway = WhatsAppGateway::getDefault();
            if (!$gateway) {
                throw new \Exception("No active WhatsApp gateway found.");
            }

            // Create gateway instance
            $provider = WhatsAppGatewayFactory::create($gateway);

            // Create pending log
            $message = new WhatsAppMessage();
            $message->gateway_id = $gateway->id;
            $message->recipient_phone = $to;
            $message->template_params = json_encode(['name' => $templateName, 'language' => $languageCode, 'components' => $components]);
            $message->campaign_id = $campaignId;
            $message->status = 'pending';
            $message->save();

            // Send via provider
            $response = $provider->sendTemplateMessage($to, $templateName, $languageCode, $components);

            // Update log
            if ($response['success']) {
                $message->status = 'sent'; // Or 'queued' depending on provider
                $message->message_id = $response['message_id'];
                $message->cost = $response['cost'] ?? $estimatedCost;
                $message->save();

                // Billing
                if ($userId) {
                    $walletService->deductCredit($userId, $message->cost, "WhatsApp Message: " . $message->message_id);
                }

                return ['success' => true, 'message_id' => $message->message_id];
            } else {
                $message->status = 'failed';
                $message->error_message = $response['error'] ?? 'Unknown error';
                $message->save();

                return ['success' => false, 'error' => $message->error_message];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a text message (Session message - 24h window)
     */
    public function sendText(string $to, string $text): array
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $estimatedCost = 0.03; // Fixed cost for text

            $walletService = new \Modules\Wallet\Services\WalletService();
            if ($userId && !$walletService->hasBalance($userId, $estimatedCost)) {
                return ['success' => false, 'error' => 'Insufficient funds'];
            }

            $gateway = WhatsAppGateway::getDefault();
            if (!$gateway) {
                throw new \Exception("No active WhatsApp gateway found.");
            }

            $provider = WhatsAppGatewayFactory::create($gateway);

            $message = new WhatsAppMessage();
            $message->gateway_id = $gateway->id;
            $message->recipient_phone = $to;
            $message->content = $text;
            $message->status = 'pending';
            $message->save();

            $response = $provider->sendTextMessage($to, $text);

            if ($response['success']) {
                $message->status = 'sent';
                $message->message_id = $response['message_id'];
                $message->cost = $response['cost'] ?? $estimatedCost;
                $message->save();

                // Billing
                if ($userId) {
                    $walletService->deductCredit($userId, $message->cost, "WhatsApp Message: " . $message->message_id);
                }

                return ['success' => true, 'message_id' => $message->message_id];
            } else {
                $message->status = 'failed';
                $message->error_message = $response['error'] ?? 'Unknown error';
                $message->save();

                return ['success' => false, 'error' => $message->error_message];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
