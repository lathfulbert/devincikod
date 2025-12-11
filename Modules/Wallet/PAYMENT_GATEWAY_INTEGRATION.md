# Payment Gateway Integration - Complete Guide

## Table of Contents

1. [Overview](#overview)
2. [Supported Payment Gateways](#supported-payment-gateways)
3. [Architecture](#architecture)
4. [Installation & Configuration](#installation--configuration)
5. [Payment Flow](#payment-flow)
6. [Configuration Guide per Gateway](#configuration-guide-per-gateway)
7. [Testing](#testing)
8. [Webhook Security](#webhook-security)
9. [Troubleshooting](#troubleshooting)
10. [API Reference](#api-reference)

---

## Overview

The Wallet module now supports real payment gateway integrations for automatic wallet top-ups. Users can choose between:

- **Online Payment (Gateway)**: Instant credit after successful payment via CinetPay, Orange Money, Wave, or PayDunya
- **Offline Payment**: Manual validation by administrators for cash, mobile money transfers, bank transfers, etc.

### Key Features

✅ **Multi-Gateway Support**: 4 payment gateways integrated (CinetPay, Orange Money, Wave, PayDunya)
✅ **Automatic Credit**: Wallet is credited automatically after successful payment
✅ **Webhook Verification**: HMAC signature verification for security
✅ **Test Mode**: All gateways support sandbox/production modes
✅ **Transaction Tracking**: Full audit trail of all payment attempts
✅ **Email Notifications**: Users notified at every step
✅ **Extensible**: Easy to add new payment gateways

---

## Supported Payment Gateways

### 1. CinetPay
- **Countries**: Côte d'Ivoire, Sénégal, Mali, Burkina Faso, Bénin, Togo
- **Payment Methods**: Mobile Money (Orange, MTN, Moov), Credit Cards, Flooz, Tmoney
- **Website**: https://cinetpay.com
- **Documentation**: https://docs.cinetpay.com

### 2. Orange Money (via Orange Money API)
- **Countries**: Côte d'Ivoire (primary), other Orange countries
- **Payment Methods**: Orange Money wallet
- **Website**: https://developer.orange.com
- **Authentication**: OAuth2 (client credentials)

### 3. Wave
- **Countries**: Côte d'Ivoire, Sénégal, Mali, Burkina Faso
- **Payment Methods**: Wave wallet
- **Website**: https://www.wave.com
- **Security**: HMAC signature verification

### 4. PayDunya
- **Countries**: Multiple African countries (francophone Africa)
- **Payment Methods**: Mobile Money, Credit Cards
- **Website**: https://paydunya.com
- **Documentation**: https://developers.paydunya.com

---

## Architecture

### Component Diagram

```
┌─────────────────────────────────────────────────────┐
│                  User Interface                      │
│            (topup.php - Gateway selection)           │
└────────────────────┬────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────┐
│              WalletController                        │
│  - processTopup()                                    │
│  - paymentReturn()                                   │
│  - paymentCancel()                                   │
│  - handlePaymentCallback()                           │
└────────────────────┬────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────┐
│          PaymentGatewayManager                       │
│  - getActiveGateways()                               │
│  - initiatePayment()                                 │
│  - verifyPayment()                                   │
│  - handleCallback()                                  │
└────────────────────┬────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        │            │            │
        ▼            ▼            ▼
┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│  CinetPay   │ │OrangeMonery │ │    Wave     │
│   Gateway   │ │   Gateway   │ │   Gateway   │
└─────────────┘ └─────────────┘ └─────────────┘
```

### Class Structure

```
PaymentGatewayInterface (Contract)
    │
    ▼
AbstractPaymentGateway (Base implementation)
    │
    ├── CinetPayGateway
    ├── OrangeMoneyGateway
    ├── WaveGateway
    └── PayDunyaGateway
```

### Key Classes

#### 1. **PaymentGatewayInterface**
Location: `Modules/Wallet/Contracts/PaymentGatewayInterface.php`

Defines the contract all gateways must implement:
- `initiatePayment(array $data): array`
- `verifyPayment(string $transactionId): array`
- `handleCallback(array $payload): array`
- `getName(): string`
- `getCode(): string`
- `isConfigured(): bool`
- `getSupportedPaymentMethods(): array`

#### 2. **AbstractPaymentGateway**
Location: `Modules/Wallet/Services/Gateways/AbstractPaymentGateway.php`

Base class providing common functionality:
- HTTP request handling
- Logging
- Hash generation/verification
- Amount formatting
- Field validation

#### 3. **PaymentGatewayManager**
Location: `Modules/Wallet/Services/PaymentGatewayManager.php`

Central manager for all gateway operations:
- Registry of all available gateways
- Gateway instantiation
- Delegation of payment operations

---

## Installation & Configuration

### Step 1: Database Configuration

Ensure the `wallet_topup_requests` table includes gateway fields:

```sql
ALTER TABLE wallet_topup_requests
ADD COLUMN IF NOT EXISTS gateway_id BIGINT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS gateway_transaction_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS gateway_response JSON NULL,
ADD COLUMN IF NOT EXISTS gateway_status VARCHAR(50) NULL;
```

### Step 2: Environment Configuration

Add gateway credentials to your `.env` file:

```env
# App URL (required for webhooks)
APP_URL=https://yourdomain.com

# CinetPay Configuration
CINETPAY_API_KEY=your_api_key_here
CINETPAY_SITE_ID=your_site_id_here
CINETPAY_SECRET_KEY=your_secret_key_here
CINETPAY_MODE=sandbox  # or 'production'

# Orange Money Configuration
ORANGE_MONEY_CLIENT_ID=your_client_id_here
ORANGE_MONEY_CLIENT_SECRET=your_client_secret_here
ORANGE_MONEY_MERCHANT_KEY=your_merchant_key_here
ORANGE_MONEY_MODE=sandbox  # or 'production'

# Wave Configuration
WAVE_API_KEY=your_api_key_here
WAVE_SECRET_KEY=your_secret_key_here
WAVE_MODE=sandbox  # or 'production'

# PayDunya Configuration
PAYDUNYA_MASTER_KEY=your_master_key_here
PAYDUNYA_PRIVATE_KEY=your_private_key_here
PAYDUNYA_TOKEN=your_token_here
PAYDUNYA_MODE=sandbox  # or 'production'
```

### Step 3: Verify Routes

Ensure these routes are registered in `WalletModule.php`:

```php
// Payment Gateway Callbacks
['GET', '/admin/wallet/payment-return', [WalletController::class, 'paymentReturn'], [$authMiddleware]],
['GET', '/admin/wallet/payment-cancel', [WalletController::class, 'paymentCancel'], [$authMiddleware]],
['POST', '/api/webhook/payment/{gatewayCode}', [WalletController::class, 'handlePaymentCallback'], []],
```

**Important**: The webhook route has NO authentication middleware since it's called by external services.

### Step 4: Configure Webhooks with Gateways

Register these webhook URLs with your payment gateway accounts:

- **CinetPay**: `https://yourdomain.com/api/webhook/payment/cinetpay`
- **Orange Money**: `https://yourdomain.com/api/webhook/payment/orange_money`
- **Wave**: `https://yourdomain.com/api/webhook/payment/wave`
- **PayDunya**: `https://yourdomain.com/api/webhook/payment/paydunya`

---

## Payment Flow

### User-Initiated Payment Flow

```
1. User visits /admin/wallet/topup
   │
   ▼
2. User selects gateway and enters amount
   │
   ▼
3. Form submitted to processTopup()
   │
   ▼
4. WalletTopupRequest created (status: pending)
   │
   ▼
5. PaymentGatewayManager->initiatePayment()
   │
   ├─ Generate unique reference (TOPUP_{id}_{timestamp})
   ├─ Prepare payment data (amount, URLs, customer info)
   └─ Call gateway API
   │
   ▼
6. Gateway returns payment URL
   │
   ▼
7. Request updated (status: processing)
   │
   ▼
8. User redirected to gateway payment page
   │
   ▼
9. User completes payment on gateway site
   │
   ▼
10. Gateway sends webhook to /api/webhook/payment/{code}
    │
    ▼
11. handlePaymentCallback() verifies signature
    │
    ▼
12. If payment successful:
    ├─ Request updated (status: approved → completed)
    ├─ Wallet credited
    └─ Email notification sent
    │
    ▼
13. User redirected back to /admin/wallet/payment-return
    │
    ▼
14. User sees success message
```

### Webhook Flow (Critical for Auto-Credit)

```
┌─────────────────────────────────────────────────────┐
│           Payment Gateway (External)                 │
│  User completes payment on gateway website           │
└────────────────────┬────────────────────────────────┘
                     │
                     │ (Webhook POST)
                     │
                     ▼
┌─────────────────────────────────────────────────────┐
│    POST /api/webhook/payment/{gatewayCode}           │
│                                                      │
│  1. Receive raw POST data                            │
│  2. Parse JSON payload                               │
│  3. Verify HMAC signature (security)                 │
│  4. Extract transaction details                      │
│  5. Find WalletTopupRequest by transaction_id        │
│  6. If status = success:                             │
│     a. Update request (approved → completed)         │
│     b. Credit wallet                                 │
│     c. Send email notification                       │
│  7. Return HTTP 200 to gateway                       │
└─────────────────────────────────────────────────────┘
```

**CRITICAL**: Webhooks MUST be accessible from the internet. Use tools like ngrok for local testing.

---

## Configuration Guide per Gateway

### CinetPay Configuration

#### 1. Create Account
- Visit https://cinetpay.com
- Register for a merchant account
- Complete KYC verification

#### 2. Get API Credentials
1. Login to CinetPay dashboard
2. Navigate to **Settings → API**
3. Copy:
   - API Key
   - Site ID
   - Secret Key (for webhook verification)

#### 3. Configure in .env

```env
CINETPAY_API_KEY=123456789abcdef
CINETPAY_SITE_ID=987654
CINETPAY_SECRET_KEY=your_secret_key_here
CINETPAY_MODE=sandbox
```

#### 4. Configure Webhook
1. In CinetPay dashboard, go to **Settings → Webhook**
2. Set notification URL: `https://yourdomain.com/api/webhook/payment/cinetpay`
3. Enable webhook notifications

#### 5. Test in Sandbox
CinetPay provides test numbers for mobile money:
- Orange Money: Use test number `+2250700000000`
- MTN: Use test number `+2250500000000`

---

### Orange Money Configuration

#### 1. Create Developer Account
- Visit https://developer.orange.com
- Register for developer account
- Subscribe to "Orange Money Web Payment" API

#### 2. Get OAuth2 Credentials
1. Create an application in Orange Developer Portal
2. Note down:
   - Client ID
   - Client Secret
   - Merchant Key

#### 3. Configure in .env

```env
ORANGE_MONEY_CLIENT_ID=your_client_id_here
ORANGE_MONEY_CLIENT_SECRET=your_client_secret_here
ORANGE_MONEY_MERCHANT_KEY=your_merchant_key_here
ORANGE_MONEY_MODE=sandbox
```

#### 4. Configure Webhook
Orange Money callback URL: `https://yourdomain.com/api/webhook/payment/orange_money`

#### 5. Authentication Flow
Orange Money uses OAuth2:
1. Gateway requests access token using client credentials
2. Token expires after ~1 hour
3. Token used in Authorization header for payment API

---

### Wave Configuration

#### 1. Create Merchant Account
- Contact Wave business support
- Complete merchant onboarding
- Get API credentials

#### 2. Get API Keys
Wave provides:
- API Key (public)
- Secret Key (for HMAC verification)

#### 3. Configure in .env

```env
WAVE_API_KEY=wave_sn_prod_your_api_key
WAVE_SECRET_KEY=your_secret_key_here
WAVE_MODE=sandbox
```

#### 4. Configure Webhook
Set Wave webhook URL: `https://yourdomain.com/api/webhook/payment/wave`

**Security**: Wave uses HMAC-SHA256 signature in `wave_signature` header.

---

### PayDunya Configuration

#### 1. Create Account
- Visit https://paydunya.com
- Register for merchant account
- Complete verification

#### 2. Get API Credentials
From PayDunya dashboard:
- Master Key
- Private Key
- Token

#### 3. Configure in .env

```env
PAYDUNYA_MASTER_KEY=your_master_key_here
PAYDUNYA_PRIVATE_KEY=your_private_key_here
PAYDUNYA_TOKEN=your_token_here
PAYDUNYA_MODE=sandbox
```

#### 4. Configure Webhook
Callback URL: `https://yourdomain.com/api/webhook/payment/paydunya`

---

## Testing

### Local Testing with ngrok

Since webhooks need to be accessible from the internet, use ngrok for local development:

#### 1. Install ngrok
```bash
# Download from https://ngrok.com
# Or install via package manager
```

#### 2. Start ngrok
```bash
ngrok http 80
```

This gives you a public URL like: `https://abc123.ngrok.io`

#### 3. Update .env
```env
APP_URL=https://abc123.ngrok.io
```

#### 4. Register Webhook URLs
Use the ngrok URL for webhooks:
- `https://abc123.ngrok.io/api/webhook/payment/cinetpay`
- `https://abc123.ngrok.io/api/webhook/payment/wave`
- etc.

### Test Scenarios

#### Test 1: Successful Payment
1. Visit `/admin/wallet/topup`
2. Select a gateway (e.g., CinetPay)
3. Enter amount: 1000 XOF
4. Submit form
5. Complete payment on gateway page (use test credentials)
6. Verify:
   - Webhook received (check logs)
   - Wallet credited
   - Request status = `completed`
   - Email sent

#### Test 2: Failed Payment
1. Initiate payment
2. Cancel on gateway page
3. Verify:
   - User redirected to cancel URL
   - Request status = `cancelled`
   - No wallet credit

#### Test 3: Webhook Signature Verification
1. Send fake webhook with invalid signature
2. Verify: HTTP 400 error, payment not processed

### Manual Webhook Testing

Use cURL to test webhook endpoints:

```bash
# Test CinetPay webhook
curl -X POST https://yourdomain.com/api/webhook/payment/cinetpay \
  -H "Content-Type: application/json" \
  -d '{
    "cpm_trans_id": "123456",
    "cpm_site_id": "987654",
    "signature": "valid_signature_here",
    "cpm_amount": "1000",
    "cpm_trans_status": "ACCEPTED",
    "cpm_custom": "TOPUP_1_1234567890"
  }'
```

Expected response:
```json
{
  "status": "success",
  "message": "Payment processed"
}
```

---

## Webhook Security

All gateways implement webhook verification to prevent fraud.

### CinetPay Signature Verification

```php
// CinetPay sends signature in payload
$signature = $payload['signature'];
$apiKey = $_ENV['CINETPAY_API_KEY'];
$siteId = $_ENV['CINETPAY_SITE_ID'];

// Reconstruct signature
$expectedSignature = hash('sha256', $apiKey . $siteId . $transactionId . $amount);

if ($signature !== $expectedSignature) {
    // Invalid - reject webhook
}
```

### Wave HMAC Verification

```php
// Wave sends HMAC in header
$receivedSignature = $_SERVER['HTTP_WAVE_SIGNATURE'];
$secretKey = $_ENV['WAVE_SECRET_KEY'];

// Compute HMAC of raw payload
$expectedSignature = hash_hmac('sha256', $rawPayload, $secretKey);

if (!hash_equals($expectedSignature, $receivedSignature)) {
    // Invalid - reject webhook
}
```

### Orange Money Token Verification

Orange Money doesn't use webhook signatures but requires OAuth2 token for all requests.

### PayDunya Signature

PayDunya provides hash in response that should be verified against Master Key.

---

## Troubleshooting

### Issue 1: Gateway not appearing in dropdown

**Symptom**: Gateway not showing in payment method dropdown

**Solution**:
1. Check `.env` has all required credentials
2. Verify gateway mode is set (`sandbox` or `production`)
3. Call `PaymentGatewayManager->getActiveGateways()` - should return the gateway
4. Check `isConfigured()` method returns true

```php
$manager = new PaymentGatewayManager();
$gateways = $manager->getActiveGateways();
var_dump($gateways); // Should include your gateway
```

---

### Issue 2: Payment initiation fails

**Symptom**: Error "Échec de l'initialisation du paiement"

**Solution**:
1. Check error logs: `error_log()` in WalletController
2. Verify API credentials are correct
3. Test API directly with cURL
4. Check API endpoint URLs (sandbox vs production)

Example for CinetPay:
```bash
curl -X POST https://api-checkout.cinetpay.com/v2/payment \
  -H "Content-Type: application/json" \
  -d '{
    "apikey": "YOUR_API_KEY",
    "site_id": "YOUR_SITE_ID",
    "transaction_id": "TEST123",
    "amount": 1000,
    "currency": "XOF",
    "description": "Test"
  }'
```

---

### Issue 3: Webhook not received

**Symptom**: Payment completed but wallet not credited

**Solution**:
1. Verify webhook URL is publicly accessible
   - Test: `curl https://yourdomain.com/api/webhook/payment/cinetpay`
   - Should return 400 (not 404)
2. Check gateway dashboard - verify webhook URL is registered
3. Check server logs for webhook requests
4. Ensure no firewall blocking gateway IPs
5. For local dev: Use ngrok

---

### Issue 4: Webhook received but payment not processed

**Symptom**: Webhook logged but wallet not credited

**Solution**:
1. Check webhook signature verification
2. Verify transaction ID matches request
3. Check request status (might already be completed)
4. Review error logs in `handlePaymentCallback()`

Debug webhook:
```php
// In handlePaymentCallback()
error_log("Raw webhook data: " . file_get_contents('php://input'));
error_log("Parsed payload: " . print_r($payload, true));
error_log("Validation result: " . print_r($result, true));
```

---

### Issue 5: Orange Money OAuth error

**Symptom**: "Failed to get access token"

**Solution**:
1. Verify Client ID and Client Secret
2. Check OAuth endpoint URL
3. Ensure base64 encoding is correct
4. Check token hasn't expired (refresh if needed)

Test OAuth directly:
```bash
curl -X POST https://api.orange.com/oauth/v3/token \
  -H "Authorization: Basic $(echo -n 'CLIENT_ID:CLIENT_SECRET' | base64)" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials"
```

---

### Issue 6: Amount mismatch error

**Symptom**: Gateway rejects payment due to amount format

**Solution**:
- CinetPay: Expects integer (cents): `1000` = 10.00 XOF
- Wave: Expects float: `1000.00`
- Orange Money: Expects string: `"1000"`

Check `formatAmount()` in gateway class.

---

## API Reference

### PaymentGatewayManager Methods

#### `getActiveGateways(): array`

Returns array of configured gateways with info:

```php
$manager = new PaymentGatewayManager();
$gateways = $manager->getActiveGateways();

// Returns:
[
    [
        'code' => 'cinetpay',
        'name' => 'CinetPay',
        'is_configured' => true,
        'supported_methods' => ['MOBILE_MONEY', 'CARD']
    ],
    // ...
]
```

#### `initiatePayment(string $gatewayCode, array $data): array`

Initiates payment:

```php
$result = $manager->initiatePayment('cinetpay', [
    'amount' => 5000,
    'currency' => 'XOF',
    'reference' => 'TOPUP_123_1234567890',
    'description' => 'Wallet topup',
    'return_url' => 'https://site.com/return',
    'cancel_url' => 'https://site.com/cancel',
    'webhook_url' => 'https://site.com/webhook',
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '+221771234567'
]);

// Returns:
[
    'success' => true,
    'payment_url' => 'https://gateway.com/pay/abc123',
    'transaction_id' => 'abc123',
    'error' => null
]
```

#### `verifyPayment(string $gatewayCode, string $transactionId): array`

Manually verify payment status:

```php
$result = $manager->verifyPayment('cinetpay', 'TOPUP_123_1234567890');

// Returns:
[
    'success' => true,
    'status' => 'success', // or 'pending', 'failed'
    'amount' => 5000,
    'transaction_id' => 'TOPUP_123_1234567890',
    'gateway_reference' => 'CPN_123456',
    'error' => null
]
```

#### `handleCallback(string $gatewayCode, array $payload): array`

Process webhook callback:

```php
$result = $manager->handleCallback('cinetpay', $_POST);

// Returns:
[
    'valid' => true,
    'transaction_id' => 'TOPUP_123_1234567890',
    'status' => 'success',
    'gateway_reference' => 'CPN_123456',
    'error' => null
]
```

---

### WalletController Methods

#### `processTopup()`

Handles topup form submission. Creates request and initiates gateway payment.

**POST Parameters**:
- `user_id` (int): User ID
- `amount` (float): Amount in XOF
- `payment_method` (string): 'gateway' or 'cash'/'mobile_money'/'bank_transfer'/'other'
- `gateway_code` (string): Gateway code if payment_method = 'gateway'
- `notes` (string): Optional notes

#### `paymentReturn()`

Handles user redirect after payment.

**GET Parameters**:
- `request_id` (int): WalletTopupRequest ID

#### `paymentCancel()`

Handles user cancellation.

**GET Parameters**:
- `request_id` (int): WalletTopupRequest ID

#### `handlePaymentCallback($gatewayCode)`

Processes webhook from gateway.

**POST Body**: JSON payload from gateway

**Response**: JSON
```json
{
  "status": "success",
  "message": "Payment processed"
}
```

---

## Adding a New Gateway

To add a new payment gateway:

### Step 1: Create Gateway Class

Create `Modules/Wallet/Services/Gateways/NewGateway.php`:

```php
<?php

namespace Modules\Wallet\Services\Gateways;

class NewGateway extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'New Gateway';
    }

    public function getCode(): string
    {
        return 'newgateway';
    }

    public function isConfigured(): bool
    {
        return !empty($this->getConfigValue('api_key'));
    }

    public function getSupportedPaymentMethods(): array
    {
        return ['MOBILE_MONEY', 'CARD'];
    }

    public function initiatePayment(array $data): array
    {
        // Implement payment initiation
        // Return ['success' => true, 'payment_url' => '...', 'transaction_id' => '...']
    }

    public function verifyPayment(string $transactionId): array
    {
        // Implement payment verification
        // Return ['success' => true, 'status' => 'success', ...]
    }

    public function handleCallback(array $payload): array
    {
        // Implement webhook handling
        // Return ['valid' => true, 'transaction_id' => '...', 'status' => 'success']
    }
}
```

### Step 2: Register in PaymentGatewayManager

Edit `PaymentGatewayManager.php`:

```php
private array $registeredGateways = [
    'cinetpay' => CinetPayGateway::class,
    'orange_money' => OrangeMoneyGateway::class,
    'wave' => WaveGateway::class,
    'paydunya' => PayDunyaGateway::class,
    'newgateway' => NewGateway::class, // ADD THIS
];
```

### Step 3: Add Configuration to .env

```env
NEWGATEWAY_API_KEY=your_key_here
NEWGATEWAY_MODE=sandbox
```

### Step 4: Add Webhook Route

Already handled by existing route:
```
POST /api/webhook/payment/newgateway
```

### Step 5: Test

1. Configure credentials in `.env`
2. Check gateway appears in dropdown
3. Test payment flow
4. Test webhook

---

## Security Best Practices

### 1. Always Verify Webhooks
Never trust webhook data without signature verification.

### 2. Use HTTPS
All webhook URLs must use HTTPS in production.

### 3. Validate Transaction Amounts
Compare webhook amount with original request amount.

### 4. Idempotency
Ensure webhook handler is idempotent (can be called multiple times safely).

```php
// Check if already processed
if ($request->status === 'completed') {
    // Already processed - return success
    return ['status' => 'success', 'message' => 'Already processed'];
}
```

### 5. Log Everything
Log all webhook requests for audit trail.

### 6. Use Environment Variables
Never hardcode API keys in code.

### 7. Rate Limiting
Consider rate limiting webhook endpoints to prevent abuse.

---

## Performance Considerations

### 1. Async Webhooks
Webhooks should return 200 quickly. Process heavy operations async if needed.

### 2. Caching
Cache OAuth tokens (Orange Money) to avoid repeated auth requests.

### 3. Database Indexes
Ensure `gateway_transaction_id` is indexed for fast webhook lookups:

```sql
CREATE INDEX idx_gateway_transaction ON wallet_topup_requests(gateway_transaction_id);
```

---

## Monitoring & Logging

### Key Metrics to Monitor

1. **Payment Success Rate**: % of successful payments
2. **Webhook Delivery Rate**: % of webhooks received
3. **Average Payment Time**: Time from initiation to completion
4. **Failed Payment Reasons**: Track error codes

### Log Locations

- **Payment Initiation**: `WalletController::processTopup()`
- **Webhook Received**: `WalletController::handlePaymentCallback()`
- **Gateway Errors**: `AbstractPaymentGateway::logError()`

### Sample Log Analysis

```bash
# Count webhook requests per gateway
grep "Payment webhook received" /var/log/php_errors.log | awk '{print $6}' | sort | uniq -c

# Find failed webhooks
grep "Invalid webhook signature" /var/log/php_errors.log

# Track payment success rate
grep "Payment processed" /var/log/php_errors.log | wc -l
```

---

## Support & Resources

### Gateway Documentation

- **CinetPay**: https://docs.cinetpay.com
- **Orange Money**: https://developer.orange.com/apis/
- **Wave**: Contact Wave business support
- **PayDunya**: https://developers.paydunya.com

### Common Issues

For issues, check:
1. This documentation (Troubleshooting section)
2. Gateway-specific documentation
3. Server error logs
4. Gateway dashboard logs

---

## Changelog

### Version 1.0.0 (2024-12-11)
- ✅ Initial implementation
- ✅ 4 gateways integrated (CinetPay, Orange Money, Wave, PayDunya)
- ✅ Webhook verification
- ✅ Email notifications
- ✅ Test mode support
- ✅ Complete documentation

---

## License

This module is part of the SunuFramework2 project.

---

**Document Version**: 1.0.0
**Last Updated**: December 11, 2024
**Author**: Claude Code Integration Team
