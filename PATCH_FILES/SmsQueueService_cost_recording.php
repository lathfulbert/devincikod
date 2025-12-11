// ============================================================================
// Fichier: Modules/SmsCore/Services/SmsQueueService.php
// Méthode: processQueue()
// Lignes: ~173-219
// ============================================================================

// Dans la boucle foreach ($pendingSms as $sms):

if ($result['success']) {
    $sms->markAsSent();

    // Create sms_messages record with cost
    SmsMessage::create([
        'user_id' => $sms->created_by,
        'to' => $sms->recipient,
        'from' => $sms->sender_id,
        'message' => $sms->message,
        'gateway' => $gateway->provider_code,
        'status' => 'sent',
        'message_id' => 'QUEUE-' . $sms->id,
        'gateway_message_id' => substr($result['gateway_message_id'] ?? '', 0, 100),
        'cost' => $result['cost'] ?? 0,  // Cost from SmsSenderService
        'sent_at' => date('Y-m-d H:i:s'),
        'gateway_response' => $result['gateway_response'] ?? null
    ]);

    $results['success']++;
    $results['details'][] = [
        'recipient' => $sms->recipient,
        'status' => 'sent',
        'message_id' => $result['gateway_message_id'] ?? ''
    ];
} else {
    $sms->markAsFailed($result['message'] ?? 'Unknown error');

    // Create sms_messages record for failed attempt
    SmsMessage::create([
        'user_id' => $sms->created_by,
        'to' => $sms->recipient,
        'from' => $sms->sender_id,
        'message' => $sms->message,
        'gateway' => $gateway->provider_code,
        'status' => 'failed',
        'message_id' => 'QUEUE-' . $sms->id,
        'error' => $result['message'] ?? 'Unknown error',
        'gateway_response' => $result['gateway_response'] ?? null
    ]);

    $results['failed']++;
    $results['details'][] = [
        'recipient' => $sms->recipient,
        'status' => 'failed',
        'error' => $result['message'] ?? 'Unknown error'
    ];
}
