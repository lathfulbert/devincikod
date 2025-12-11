# Payment Gateway Integration - Quick Start Guide

## 🚀 Get Started in 5 Minutes

This guide will help you configure and test the payment gateway integration quickly.

---

## Step 1: Configure Environment (2 minutes)

### Option A: Test with CinetPay (Recommended for first test)

Add to your `.env` file:

```env
APP_URL=http://localhost

# CinetPay Sandbox Credentials
CINETPAY_API_KEY=12912847765bc0db748fdd44.40081707
CINETPAY_SITE_ID=445160
CINETPAY_SECRET_KEY=your_secret_key_here
CINETPAY_MODE=sandbox
```

> **Note**: Replace with your actual CinetPay sandbox credentials from https://cinetpay.com

### Option B: Test All Gateways

```env
APP_URL=http://localhost

# CinetPay
CINETPAY_API_KEY=your_api_key
CINETPAY_SITE_ID=your_site_id
CINETPAY_SECRET_KEY=your_secret_key
CINETPAY_MODE=sandbox

# Orange Money
ORANGE_MONEY_CLIENT_ID=your_client_id
ORANGE_MONEY_CLIENT_SECRET=your_client_secret
ORANGE_MONEY_MERCHANT_KEY=your_merchant_key
ORANGE_MONEY_MODE=sandbox

# Wave
WAVE_API_KEY=your_api_key
WAVE_SECRET_KEY=your_secret_key
WAVE_MODE=sandbox

# PayDunya
PAYDUNYA_MASTER_KEY=your_master_key
PAYDUNYA_PRIVATE_KEY=your_private_key
PAYDUNYA_TOKEN=your_token
PAYDUNYA_MODE=sandbox
```

---

## Step 2: Setup ngrok for Local Testing (1 minute)

Webhooks need public URLs. Use ngrok:

```bash
# Download ngrok from https://ngrok.com/download
# Then run:
ngrok http 80
```

You'll see output like:
```
Forwarding: https://abc123.ngrok.io -> http://localhost:80
```

Update your `.env`:
```env
APP_URL=https://abc123.ngrok.io
```

---

## Step 3: Register Webhook URLs (1 minute)

### For CinetPay:
1. Login to CinetPay dashboard: https://cinetpay.com
2. Go to **Settings → Webhook**
3. Set URL: `https://abc123.ngrok.io/api/webhook/payment/cinetpay`
4. Save

### For Other Gateways:
- **Orange Money**: `https://abc123.ngrok.io/api/webhook/payment/orange_money`
- **Wave**: `https://abc123.ngrok.io/api/webhook/payment/wave`
- **PayDunya**: `https://abc123.ngrok.io/api/webhook/payment/paydunya`

---

## Step 4: Test Payment Flow (1 minute)

1. **Visit Topup Page**
   ```
   http://localhost/admin/wallet/topup
   ```

2. **Select Gateway**
   - You should see configured gateways in dropdown
   - Select "CinetPay - Paiement instantané" (or your configured gateway)

3. **Enter Amount**
   - Minimum: 100 XOF
   - Example: 1000 XOF

4. **Submit**
   - You'll be redirected to the gateway payment page

5. **Complete Payment**
   - Use test credentials provided by the gateway
   - For CinetPay sandbox:
     - Test Orange Money: `+2250700000000`
     - Test MTN: `+2250500000000`

6. **Verify Success**
   - Check wallet balance increased
   - Check email notification sent
   - Check request status = `completed`

---

## Step 5: Verify Webhook (Optional)

Monitor webhook requests in your terminal:

```bash
# In a separate terminal, watch PHP error log
tail -f /path/to/php_errors.log

# You should see:
# "Payment webhook received from cinetpay: ..."
# "Wallet credited successfully for request 123"
```

---

## 🎯 Quick Test Checklist

Use this checklist to verify everything works:

- [ ] Gateway appears in topup form dropdown
- [ ] Form submission redirects to gateway page
- [ ] Payment can be completed on gateway page
- [ ] Webhook is received (check logs)
- [ ] Wallet balance increased
- [ ] Request status = `completed`
- [ ] Email notification received
- [ ] Transaction appears in wallet history

---

## 🐛 Common Issues & Quick Fixes

### Issue: Gateway not showing in dropdown

**Fix:**
```php
// Test if gateway is configured
$manager = new \Modules\Wallet\Services\PaymentGatewayManager();
$gateways = $manager->getActiveGateways();
var_dump($gateways); // Should show your gateway
```

**Likely cause**: Missing credentials in `.env`

---

### Issue: Webhook not received

**Fix 1**: Verify ngrok is running
```bash
# Check ngrok status
curl https://abc123.ngrok.io/api/webhook/payment/cinetpay
# Should return 400 (not 404)
```

**Fix 2**: Check webhook URL in gateway dashboard

**Fix 3**: Check firewall isn't blocking requests

---

### Issue: Payment completes but wallet not credited

**Fix**: Check webhook signature
```php
// In handlePaymentCallback(), add debug logging
error_log("Webhook payload: " . print_r($payload, true));
error_log("Validation result: " . print_r($result, true));
```

**Likely cause**: Invalid webhook signature (check secret key)

---

## 📊 Verify Integration

### Check Active Gateways

Create a test file `test_gateways.php`:

```php
<?php
require_once 'vendor/autoload.php';

use Modules\Wallet\Services\PaymentGatewayManager;

$manager = new PaymentGatewayManager();

// List all gateways
echo "=== All Gateways ===\n";
$allGateways = $manager->getGatewaysInfo();
foreach ($allGateways as $gw) {
    echo sprintf(
        "%s (%s): %s\n",
        $gw['name'],
        $gw['code'],
        $gw['is_configured'] ? '✅ CONFIGURED' : '❌ NOT CONFIGURED'
    );
}

// List active gateways
echo "\n=== Active Gateways ===\n";
$activeGateways = $manager->getActiveGateways();
foreach ($activeGateways as $gw) {
    echo sprintf(
        "%s (%s) - Methods: %s\n",
        $gw['name'],
        $gw['code'],
        implode(', ', $gw['supported_methods'])
    );
}
```

Run:
```bash
php test_gateways.php
```

Expected output:
```
=== All Gateways ===
CinetPay (cinetpay): ✅ CONFIGURED
Orange Money (orange_money): ❌ NOT CONFIGURED
Wave (wave): ❌ NOT CONFIGURED
PayDunya (paydunya): ❌ NOT CONFIGURED

=== Active Gateways ===
CinetPay (cinetpay) - Methods: MOBILE_MONEY, CARD, FLOOZ, TMONEY, CREDIT_CARD
```

---

## 🔍 Monitor Webhook Requests

### Using ngrok Inspector

Visit http://127.0.0.1:4040 (ngrok web interface) to see all webhook requests in real-time.

### Using Server Logs

```bash
# Watch for webhook activity
tail -f /var/log/php_errors.log | grep "webhook"

# Count webhooks received per gateway
grep "Payment webhook received" /var/log/php_errors.log | awk '{print $6}' | sort | uniq -c
```

---

## 🎉 Success Indicators

You've successfully integrated payment gateways when:

✅ **User Flow Works**
- User can select gateway
- User redirected to gateway page
- Payment completes successfully

✅ **Backend Works**
- Webhook received
- Signature verified
- Wallet credited
- Email sent

✅ **Database Updated**
- Request status = `completed`
- `gateway_transaction_id` populated
- `gateway_response` contains data
- Transaction record created

---

## 📖 Next Steps

### 1. Production Deployment

When ready for production:

```env
# Change modes to production
CINETPAY_MODE=production
ORANGE_MONEY_MODE=production
WAVE_MODE=production
PAYDUNYA_MODE=production

# Use production URL
APP_URL=https://yourdomain.com
```

### 2. Register Production Webhooks

Update webhook URLs in gateway dashboards to use production domain.

### 3. Monitor Transactions

- Track success rates
- Monitor failed payments
- Review error logs

---

## 📚 Additional Resources

- **Complete Documentation**: `PAYMENT_GATEWAY_INTEGRATION.md`
- **Implementation Summary**: `PAYMENT_GATEWAY_IMPLEMENTATION_SUMMARY.md`
- **Troubleshooting**: See "Troubleshooting" section in `PAYMENT_GATEWAY_INTEGRATION.md`

---

## 💡 Pro Tips

### Tip 1: Test with Small Amounts
Always test with minimum amounts (100 XOF) in sandbox mode.

### Tip 2: Keep Sandbox Separate
Use different credentials for sandbox and production.

### Tip 3: Log Everything
Enable verbose logging during initial testing:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Tip 4: Test Webhook Signatures
Test with invalid signatures to ensure security works:
```bash
curl -X POST https://yourdomain.com/api/webhook/payment/cinetpay \
  -H "Content-Type: application/json" \
  -d '{"signature": "invalid", ...}'
# Should return 400 error
```

### Tip 5: Use Gateway Test Cards
Each gateway provides test credentials. Always use these in sandbox mode.

---

## 🆘 Get Help

### Gateway Not Working?

1. Check `.env` has correct credentials
2. Verify credentials in gateway dashboard
3. Check mode is set correctly (sandbox/production)
4. Review gateway-specific documentation

### Webhook Issues?

1. Verify URL is publicly accessible
2. Check webhook is registered in gateway dashboard
3. Review server logs for errors
4. Test with ngrok inspector

### Still Stuck?

1. Check `PAYMENT_GATEWAY_INTEGRATION.md` → Troubleshooting section
2. Review gateway documentation
3. Check server error logs
4. Verify database has correct schema

---

## ✅ Quick Test Command

Run this to verify everything is working:

```bash
# 1. Check .env configured
grep -E "CINETPAY|ORANGE|WAVE|PAYDUNYA" .env

# 2. Test webhook endpoint
curl -X POST http://localhost/api/webhook/payment/cinetpay
# Should return 400 (not 404)

# 3. Check active gateways
php test_gateways.php
```

---

**You're all set! 🎉**

Start with one gateway (CinetPay recommended), test the complete flow, then add more gateways as needed.

---

**Quick Start Version**: 1.0.0
**Last Updated**: December 11, 2024
