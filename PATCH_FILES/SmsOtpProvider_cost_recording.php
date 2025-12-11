// ============================================================================
// Fichier: Modules/Auth/Providers/SmsOtpProvider.php
// Lignes: ~269-278
// ============================================================================

// Update history with result
$smsMessage->gateway_response = $result['gateway_response'] ?? null;

// Set cost from pricing calculation
if (isset($result['cost'])) {
    $smsMessage->cost = $result['cost'];
}

if ($result['success']) {
    $smsMessage->markAsSent($result['gateway_message_id'] ?? '');
} else {
    $smsMessage->markAsFailed($result['message'] ?? 'Unknown error');
    // ... rest of error handling
}
