# Payment Gateway Architecture Diagrams

## System Architecture Overview

```
┌────────────────────────────────────────────────────────────────────────┐
│                          USER INTERFACE LAYER                           │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐ │
│  │              topup.php (Gateway Selection Form)                   │ │
│  │  - Select Gateway (CinetPay, Orange Money, Wave, PayDunya)       │ │
│  │  - Enter Amount (100 - 10,000,000 XOF)                           │ │
│  │  - Payment Method Selection                                       │ │
│  └──────────────────────────────────────────────────────────────────┘ │
│                                   │                                     │
│                                   │ POST /admin/wallet/topup            │
│                                   ▼                                     │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│                        CONTROLLER LAYER                                 │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐ │
│  │                      WalletController                             │ │
│  │                                                                   │ │
│  │  processTopup()                                                   │ │
│  │  ├─ Validate input                                                │ │
│  │  ├─ Create WalletTopupRequest (status: pending)                  │ │
│  │  ├─ Generate reference: TOPUP_{id}_{timestamp}                   │ │
│  │  ├─ Build payment data (amount, URLs, customer info)             │ │
│  │  └─ Call PaymentGatewayManager->initiatePayment()                │ │
│  │                                                                   │ │
│  │  paymentReturn()                                                  │ │
│  │  └─ Handle user redirect after payment                           │ │
│  │                                                                   │ │
│  │  paymentCancel()                                                  │ │
│  │  └─ Handle payment cancellation                                  │ │
│  │                                                                   │ │
│  │  handlePaymentCallback($gatewayCode)                             │ │
│  │  ├─ Receive webhook POST                                          │ │
│  │  ├─ Verify signature                                              │ │
│  │  ├─ Find request by transaction_id                                │ │
│  │  ├─ Credit wallet if successful                                   │ │
│  │  └─ Send notification                                             │ │
│  └──────────────────────────────────────────────────────────────────┘ │
│                                   │                                     │
│                                   ▼                                     │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│                     BUSINESS LOGIC LAYER                                │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐ │
│  │                   PaymentGatewayManager                           │ │
│  │                                                                   │ │
│  │  Registry:                                                        │ │
│  │  ├─ 'cinetpay' => CinetPayGateway::class                         │ │
│  │  ├─ 'orange_money' => OrangeMoneyGateway::class                  │ │
│  │  ├─ 'wave' => WaveGateway::class                                 │ │
│  │  └─ 'paydunya' => PayDunyaGateway::class                         │ │
│  │                                                                   │ │
│  │  Methods:                                                         │ │
│  │  ├─ getActiveGateways() → Returns configured gateways            │ │
│  │  ├─ initiatePayment() → Delegates to gateway                     │ │
│  │  ├─ verifyPayment() → Checks payment status                      │ │
│  │  └─ handleCallback() → Processes webhook                         │ │
│  └──────────────────────────────────────────────────────────────────┘ │
│                                   │                                     │
│                    ┌──────────────┼──────────────┐                     │
│                    │              │              │                     │
│                    ▼              ▼              ▼                     │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│                      GATEWAY LAYER                                      │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │              PaymentGatewayInterface (Contract)                  │  │
│  │  + initiatePayment(array): array                                 │  │
│  │  + verifyPayment(string): array                                  │  │
│  │  + handleCallback(array): array                                  │  │
│  │  + getName(): string                                             │  │
│  │  + getCode(): string                                             │  │
│  │  + isConfigured(): bool                                          │  │
│  │  + getSupportedPaymentMethods(): array                           │  │
│  └─────────────────────────────────────────────────────────────────┘  │
│                                 ▲                                       │
│                                 │ implements                            │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │            AbstractPaymentGateway (Base Class)                   │  │
│  │  # httpRequest() - HTTP communication                            │  │
│  │  # logError() - Error logging                                    │  │
│  │  # generateHash() - HMAC generation                              │  │
│  │  # verifyHash() - Signature verification                         │  │
│  │  # formatAmount() - Amount conversion                            │  │
│  │  # validateRequiredFields() - Input validation                   │  │
│  └─────────────────────────────────────────────────────────────────┘  │
│                                 ▲                                       │
│                                 │ extends                               │
│         ┌───────────────────────┼───────────────────────┐              │
│         │                       │                       │              │
│  ┌──────┴──────┐  ┌────────────┴──────┐  ┌────────────┴──────┐       │
│  │ CinetPay    │  │  OrangeMoney      │  │    Wave           │       │
│  │  Gateway    │  │    Gateway        │  │   Gateway         │       │
│  └─────────────┘  └───────────────────┘  └───────────────────┘       │
│  ┌──────────────────────────────────────────────────────────────────┐ │
│  │                     PayDunya Gateway                              │ │
│  └──────────────────────────────────────────────────────────────────┘ │
│                                 │                                       │
│                                 │ API Calls                             │
│                                 ▼                                       │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES LAYER                              │
│                                                                         │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐      │
│  │  CinetPay  │  │   Orange   │  │    Wave    │  │  PayDunya  │      │
│  │    API     │  │  Money API │  │    API     │  │    API     │      │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘      │
│        │               │                │              │               │
│        └───────────────┴────────────────┴──────────────┘               │
│                              │                                          │
│                    Webhooks / Callbacks                                 │
│                              │                                          │
│                              ▼                                          │
│          POST /api/webhook/payment/{gatewayCode}                       │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Payment Flow Sequence Diagram

```
User            topup.php       WalletController    PaymentGatewayManager    Gateway API    Webhook
 │                  │                   │                     │                  │            │
 │──Select Gateway──▶                   │                     │                  │            │
 │──Enter Amount───▶                   │                     │                  │            │
 │──Submit Form────▶                   │                     │                  │            │
 │                  │──POST topup──────▶                     │                  │            │
 │                  │                   │                     │                  │            │
 │                  │                   │──Create Request────▶                  │            │
 │                  │                   │  (status: pending)  │                  │            │
 │                  │                   │                     │                  │            │
 │                  │                   │──initiatePayment()─▶                  │            │
 │                  │                   │                     │──POST /payment──▶            │
 │                  │                   │                     │                  │            │
 │                  │                   │                     │◀─payment_url────│            │
 │                  │                   │◀────Return URL──────│                  │            │
 │                  │                   │                     │                  │            │
 │                  │                   │──Update status─────▶                  │            │
 │                  │                   │  (processing)       │                  │            │
 │                  │                   │                     │                  │            │
 │◀─────Redirect to Gateway Payment Page────────────────────────────────────────┘            │
 │                  │                   │                     │                  │            │
 │──Complete Payment────────────────────────────────────────────────────────────▶            │
 │                  │                   │                     │                  │            │
 │                  │                   │                     │                  │──webhook──▶
 │                  │                   │                     │                  │   POST     │
 │                  │                   │◀────────────────────────────────────────────────────┤
 │                  │                   │  handlePaymentCallback()              │            │
 │                  │                   │                     │                  │            │
 │                  │                   │──Verify Signature──▶                  │            │
 │                  │                   │──Find Request───────▶                  │            │
 │                  │                   │──Credit Wallet──────▶                  │            │
 │                  │                   │──Update Status──────▶                  │            │
 │                  │                   │  (completed)        │                  │            │
 │                  │                   │──Send Email─────────▶                  │            │
 │                  │                   │                     │                  │            │
 │                  │                   ├──────────────────────────────────────────200 OK────▶
 │                  │                   │                     │                  │            │
 │◀─────Redirect to Success Page────────┤                     │                  │            │
 │  /admin/wallet/payment-return        │                     │                  │            │
 │                  │                   │                     │                  │            │
 │──View Balance───▶                   │                     │                  │            │
 │  (Updated)       │                   │                     │                  │            │
```

---

## Gateway Class Hierarchy

```
┌────────────────────────────────────────────────────────────┐
│          PaymentGatewayInterface (Contract)                │
│  ────────────────────────────────────────────────────────  │
│  + initiatePayment(array $data): array                     │
│  + verifyPayment(string $transactionId): array             │
│  + handleCallback(array $payload): array                   │
│  + getName(): string                                       │
│  + getCode(): string                                       │
│  + isConfigured(): bool                                    │
│  + getSupportedPaymentMethods(): array                     │
└────────────────────────────────────────────────────────────┘
                           ▲
                           │ implements
                           │
┌────────────────────────────────────────────────────────────┐
│        AbstractPaymentGateway (Base Class)                 │
│  ────────────────────────────────────────────────────────  │
│  # array $config                                           │
│  ────────────────────────────────────────────────────────  │
│  # httpRequest(url, method, data, headers): array          │
│  # logError(message, context): void                        │
│  # generateHash(data, secret): string                      │
│  # verifyHash(data, hash, secret): bool                    │
│  # formatAmount(amount, toCents): float                    │
│  # validateRequiredFields(data, required): array           │
│  # getConfigValue(key, default): mixed                     │
│  # getBaseUrl(): string                                    │
└────────────────────────────────────────────────────────────┘
                           ▲
           ┌───────────────┴───────────────┬────────────────┬────────────────┐
           │                               │                │                │
┌──────────┴──────────┐  ┌────────────────┴──────┐  ┌──────┴────────┐  ┌───┴──────────┐
│   CinetPayGateway   │  │ OrangeMoneyGateway    │  │  WaveGateway  │  │PayDunyaGateway│
│  ─────────────────  │  │  ──────────────────   │  │  ──────────   │  │ ───────────── │
│  Côte d'Ivoire,     │  │  Côte d'Ivoire        │  │  CI, SN, ML   │  │  Multi-country│
│  Sénégal, Mali      │  │  OAuth2 Auth          │  │  HMAC sig     │  │  Multi-method │
│  SHA-256 signature  │  │  Token caching        │  │  Direct XOF   │  │  3-key auth   │
│                     │  │                       │  │               │  │               │
│  Methods:           │  │  Methods:             │  │  Methods:     │  │  Methods:     │
│  - Mobile Money     │  │  - Orange Money       │  │  - Wave       │  │  - Mobile $   │
│  - Cards            │  │                       │  │               │  │  - Cards      │
│  - Flooz/Tmoney     │  │                       │  │               │  │               │
└─────────────────────┘  └───────────────────────┘  └───────────────┘  └───────────────┘
```

---

## Database Schema

```
┌─────────────────────────────────────────────────────────────────────┐
│                      wallet_topup_requests                           │
├─────────────────────────────────────────────────────────────────────┤
│  id                      BIGINT UNSIGNED [PK]                        │
│  user_id                 BIGINT UNSIGNED [FK → users.id]             │
│  wallet_id               BIGINT UNSIGNED [FK → wallets.id]           │
│  amount                  DECIMAL(15,2)                               │
│  currency                VARCHAR(3) [default: 'XOF']                 │
│  payment_method          ENUM('gateway', 'cash', 'mobile_money'...)  │
│  gateway_id              BIGINT UNSIGNED NULL [FK → sms_gateways.id] │
│  gateway_transaction_id  VARCHAR(255) NULL [unique]                  │
│  gateway_response        JSON NULL                                   │
│  gateway_status          VARCHAR(50) NULL                            │
│  status                  ENUM('pending', 'processing', 'approved'...)│
│  notes                   TEXT NULL                                   │
│  reviewed_by             BIGINT UNSIGNED NULL [FK → users.id]        │
│  reviewed_at             TIMESTAMP NULL                              │
│  admin_notes             TEXT NULL                                   │
│  ip_address              VARCHAR(45) NULL                            │
│  user_agent              VARCHAR(255) NULL                           │
│  created_at              TIMESTAMP                                   │
│  updated_at              TIMESTAMP                                   │
├─────────────────────────────────────────────────────────────────────┤
│  Indexes:                                                            │
│  - PRIMARY KEY (id)                                                  │
│  - INDEX idx_user_id (user_id)                                       │
│  - INDEX idx_status (status)                                         │
│  - UNIQUE INDEX idx_gateway_txn (gateway_transaction_id)             │
│  - INDEX idx_created_at (created_at)                                 │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Request State Machine

```
┌─────────────────────────────────────────────────────────────────────┐
│                     Request Status Flow                              │
└─────────────────────────────────────────────────────────────────────┘

  [User submits form]
         │
         ▼
    ┌─────────┐
    │ pending │  ← Initial state when request created
    └────┬────┘
         │
         ├───────────────────┬─────────────────────────┐
         │                   │                         │
    [Gateway]           [Offline]              [User cancels]
         │                   │                         │
         ▼                   ▼                         ▼
   ┌────────────┐      ┌─────────┐              ┌──────────┐
   │ processing │      │ pending │              │cancelled │
   └──────┬─────┘      └────┬────┘              └──────────┘
          │                 │
   [Webhook arrives]  [Admin reviews]
          │                 │
          │           ┌─────┴─────┐
          │           │           │
          │      [Approve]   [Reject]
          │           │           │
          │           ▼           ▼
          │      ┌─────────┐  ┌──────────┐
          │      │approved │  │ rejected │
          │      └────┬────┘  └──────────┘
          │           │
          │    [Wallet credited]
          │           │
          ├───────────┘
          │
          ▼
     ┌──────────┐
     │completed │  ← Final success state
     └──────────┘

     ┌────────┐
     │ failed │  ← Final failure state
     └────────┘
```

---

## Webhook Security Flow

```
┌────────────────────────────────────────────────────────────────────┐
│                    Webhook Security Verification                    │
└────────────────────────────────────────────────────────────────────┘

Gateway                          Your Server
  │                                   │
  │  POST /api/webhook/payment/xxx    │
  │  ─────────────────────────────────▶
  │  Headers:                         │
  │    Content-Type: application/json │
  │    [Gateway-specific headers]     │
  │  Body:                            │
  │    {                              │
  │      "transaction_id": "...",     │
  │      "amount": 1000,              │
  │      "status": "success",         │
  │      "signature": "abc123..."     │
  │    }                              │
  │                                   │
  │                                   ├─[1] Receive raw POST data
  │                                   │
  │                                   ├─[2] Parse JSON
  │                                   │
  │                                   ├─[3] Extract signature
  │                                   │
  │                                   ├─[4] Compute expected signature
  │                                   │     hash_hmac('sha256', data, secret)
  │                                   │
  │                                   ├─[5] Compare signatures
  │                                   │     if (signature !== expected)
  │                                   │
  │                          ┌────────┴────────┐
  │                          │                 │
  │                    [Valid]           [Invalid]
  │                          │                 │
  │                          ▼                 ▼
  │                   Process Payment    Return 400 Error
  │                          │                 │
  │  ◀────200 OK─────────────┤                 │
  │  {                       │                 │
  │    "status": "success"   │                 │
  │  }                       │                 │
  │                          │                 │
  │  ◀────400 Bad Request────────────────────┤
  │  {                                        │
  │    "status": "error",                     │
  │    "message": "Invalid signature"         │
  │  }                                        │
```

---

## Configuration Flow

```
┌────────────────────────────────────────────────────────────────────┐
│                  Gateway Configuration Loading                      │
└────────────────────────────────────────────────────────────────────┘

  .env File
     │
     │  CINETPAY_API_KEY=xxx
     │  CINETPAY_SITE_ID=123
     │  CINETPAY_MODE=sandbox
     │
     ▼
  $_ENV superglobal
     │
     ▼
  AbstractPaymentGateway->getConfigValue()
     │
     ├─ Reads from $_ENV['CINETPAY_API_KEY']
     ├─ Prefix = uppercase(code) . '_'
     └─ Returns value or default
     │
     ▼
  Gateway->isConfigured()
     │
     ├─ Checks required fields exist
     └─ Returns true/false
     │
     ▼
  PaymentGatewayManager->getActiveGateways()
     │
     ├─ Loops through all registered gateways
     ├─ Checks isConfigured() for each
     └─ Returns only configured ones
     │
     ▼
  Controller passes to View
     │
     ▼
  View renders gateway options
```

---

## Error Handling Flow

```
┌────────────────────────────────────────────────────────────────────┐
│                        Error Handling                               │
└────────────────────────────────────────────────────────────────────┘

User Action
    │
    ▼
  Try Block
    │
    ├─ Validation Error?
    │  └─ Flash error message → Redirect to form
    │
    ├─ Gateway API Error?
    │  ├─ Log error to error_log
    │  ├─ Update request status = 'failed'
    │  ├─ Store error in gateway_response
    │  └─ Flash error message → Redirect to form
    │
    ├─ Webhook Signature Error?
    │  ├─ Log security warning
    │  └─ Return HTTP 400 (invalid)
    │
    ├─ Database Error?
    │  ├─ Rollback transaction
    │  ├─ Log error
    │  └─ Flash error message → Redirect
    │
    └─ Unknown Error?
       ├─ Log with full context
       ├─ Flash generic error message
       └─ Redirect to safe page

  Catch Block
    │
    ├─ Exception logged
    ├─ Flash error message
    └─ Redirect to safe page
```

---

## Performance Optimization Points

```
┌────────────────────────────────────────────────────────────────────┐
│                    Performance Optimizations                        │
└────────────────────────────────────────────────────────────────────┘

1. Database Indexes
   ├─ gateway_transaction_id (UNIQUE)
   ├─ user_id (for user queries)
   ├─ status (for filtering)
   └─ created_at (for sorting)

2. Webhook Processing
   ├─ Return 200 quickly (< 1s)
   ├─ Process async if heavy operations
   └─ Use database transactions

3. Gateway API Calls
   ├─ Set reasonable timeouts (10s)
   ├─ Cache OAuth tokens (Orange Money)
   └─ Retry with exponential backoff

4. Caching Opportunities
   ├─ Active gateways list (5 min TTL)
   ├─ Gateway configuration (until .env change)
   └─ OAuth access tokens (55 min TTL)

5. Query Optimization
   ├─ Use eager loading for relationships
   ├─ Paginate admin request list
   └─ Index foreign keys
```

---

## Deployment Checklist

```
┌────────────────────────────────────────────────────────────────────┐
│                     Deployment Checklist                            │
└────────────────────────────────────────────────────────────────────┘

□ Pre-Deployment
  ├─ □ Test all gateways in sandbox mode
  ├─ □ Verify webhook signatures work
  ├─ □ Test email notifications
  ├─ □ Load test webhook endpoint
  └─ □ Review error logs

□ Configuration
  ├─ □ Set production gateway credentials
  ├─ □ Set APP_URL to production domain
  ├─ □ Set gateway modes to 'production'
  ├─ □ Verify .env has all required keys
  └─ □ Check database has correct schema

□ Gateway Registration
  ├─ □ Register production webhook URLs
  ├─ □ Verify webhook URLs are accessible
  ├─ □ Test webhook delivery
  └─ □ Configure return/cancel URLs

□ Security
  ├─ □ Webhook routes have NO auth middleware
  ├─ □ HTTPS enabled for all URLs
  ├─ □ Secret keys secured in .env
  ├─ □ Error reporting disabled in production
  └─ □ Rate limiting enabled

□ Monitoring
  ├─ □ Error logging configured
  ├─ □ Webhook monitoring setup
  ├─ □ Payment success rate tracking
  └─ □ Alert system for failed payments

□ Post-Deployment
  ├─ □ Test one payment with real money (small amount)
  ├─ □ Verify webhook received and processed
  ├─ □ Check wallet credited correctly
  ├─ □ Verify email sent
  └─ □ Monitor for 24 hours
```

---

**Architecture Version**: 1.0.0
**Last Updated**: December 11, 2024
