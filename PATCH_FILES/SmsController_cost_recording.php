// ============================================================================
// PARTIE 1: Dans la méthode send() - Enregistrement du coût pour SMS unique
// Ligne ~108-114
// ============================================================================

// Update message with gateway response and cost
$smsMessage->gateway_response = $result['gateway_response'] ?? null;

// Set cost from pricing calculation
if (isset($result['cost'])) {
    $smsMessage->cost = $result['cost'];
}

if ($result['success']) {
    $smsMessage->markAsSent($result['gateway_message_id'] ?? '');
    // ...
}


// ============================================================================
// PARTIE 2: Modifier la méthode sendDirectBulk() - Signature
// Ligne ~440
// ============================================================================

/**
 * Send SMS directly (synchronous) - for small batches
 *
 * @param array $recipients Array of phone numbers
 * @param string $message SMS message template
 * @param string $sender Sender ID
 * @param object $campaign Campaign object
 * @param int|null $userId User ID
 * @param array|null $fileData Full file data with columns (for variables)
 * @param string|null $phoneColumn Phone column name
 */
private function sendDirectBulk(array $recipients, string $message, string $sender, $campaign, $userId, $fileData = null, $phoneColumn = null)
{
    // ... code existant ...

    $sent = 0;
    $failed = 0;

    // Build recipient data map for variable replacement
    $recipientDataMap = [];
    if ($fileData && $phoneColumn) {
        foreach ($fileData as $row) {
            if (isset($row[$phoneColumn])) {
                $phone = PhoneNumberService::format($row[$phoneColumn]);
                $recipientDataMap[$phone] = $row;
            }
        }
    }

    foreach ($recipients as $recipient) {
        try {
            // Replace variables in message if file data is available
            $personalizedMessage = $message;
            if (isset($recipientDataMap[$recipient])) {
                $personalizedMessage = FileImportService::replaceVariables($message, $recipientDataMap[$recipient]);
            }

            $result = $senderService->send($recipient, $personalizedMessage, $sender, [
                'user_id' => $userId,
                'gateway_name' => $gateway->provider_code,
                'campaign_id' => $campaign->id
            ]);

            if ($result['success']) {
                // Create sms_messages record with cost
                SmsMessage::create([
                    'user_id' => $userId,
                    'to' => $recipient,
                    'from' => $sender,
                    'message' => $personalizedMessage,
                    'gateway' => $gateway->provider_code,
                    'status' => 'sent',
                    'message_id' => 'CAMPAIGN-' . $campaign->id . '-' . uniqid(),
                    'gateway_message_id' => substr($result['gateway_message_id'] ?? '', 0, 100),
                    'cost' => $result['cost'] ?? 0,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'gateway_response' => $result['gateway_response'] ?? null
                ]);

                $sent++;
            } else {
                // Create sms_messages record for failed attempt
                SmsMessage::create([
                    'user_id' => $userId,
                    'to' => $recipient,
                    'from' => $sender,
                    'message' => $personalizedMessage,
                    'gateway' => $gateway->provider_code,
                    'status' => 'failed',
                    'message_id' => 'CAMPAIGN-' . $campaign->id . '-' . uniqid(),
                    'error' => $result['message'] ?? 'Unknown error',
                    'gateway_response' => $result['gateway_response'] ?? null
                ]);

                $failed++;
            }
        } catch (\Exception $e) {
            // Create sms_messages record for exception
            SmsMessage::create([
                'user_id' => $userId,
                'to' => $recipient,
                'from' => $sender,
                'message' => $personalizedMessage ?? $message,
                'gateway' => $gateway->provider_code,
                'status' => 'failed',
                'message_id' => 'CAMPAIGN-' . $campaign->id . '-' . uniqid(),
                'error' => $e->getMessage()
            ]);

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
