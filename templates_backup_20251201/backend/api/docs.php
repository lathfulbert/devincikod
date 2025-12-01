@extends('backend.layouts.master')

@section('title', 'API Documentation')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>API Documentation</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/api-keys') ?>">API Keys</a></li>
                    <li class="breadcrumb-item active">Documentation</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4>SMS API Documentation</h4>
                    <p class="text-muted">Complete guide for integrating SMS functionality into your applications.</p>

                    <hr>

                    <!-- Authentication -->
                    <h5 id="authentication"><i data-feather="lock"></i> Authentication</h5>
                    <p>All API requests require authentication using an API key. Include your API key in the Authorization header:</p>
                    <pre class="bg-light p-3 rounded"><code>Authorization: Bearer YOUR_API_KEY</code></pre>

                    <p>Alternatively, you can pass the API key as a parameter:</p>
                    <pre class="bg-light p-3 rounded"><code>?api_key=YOUR_API_KEY</code></pre>

                    <hr>

                    <!-- Send SMS -->
                    <h5 id="send-sms"><i data-feather="send"></i> Send SMS</h5>
                    <div class="mb-3">
                        <span class="badge bg-success">POST</span>
                        <code><?= url('/api/v1/sms/send') ?></code>
                    </div>

                    <h6>Request Body (JSON)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "to": "+225XXXXXXXXX",
  "message": "Your message here",
  "sender_id": "YourBrand" // Optional
}</code></pre>

                    <h6>Parameters</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>to</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Recipient phone number (international format recommended)</td>
                            </tr>
                            <tr>
                                <td><code>message</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>SMS message content (max 1600 characters)</td>
                            </tr>
                            <tr>
                                <td><code>sender_id</code></td>
                                <td>string</td>
                                <td>No</td>
                                <td>Custom sender ID (if supported by gateway)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Success Response (200 OK)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "message": "SMS sent successfully",
  "data": {
    "message_id": "msg_123456",
    "recipient": "+225XXXXXXXXX",
    "segments": 1,
    "cost": 15.00
  }
}</code></pre>

                    <h6>Error Response (400 Bad Request)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": false,
  "error": "Bad Request",
  "message": "Missing required fields: to, message"
}</code></pre>

                    <h6>Example Request (cURL)</h6>
                    <pre class="bg-light p-3 rounded"><code>curl -X POST <?= url('/api/v1/sms/send') ?> \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+225XXXXXXXXX",
    "message": "Hello from API",
    "sender_id": "MyApp"
  }'</code></pre>

                    <h6>Example Request (PHP)</h6>
                    <pre class="bg-light p-3 rounded"><code>&lt;?php
$apiKey = 'YOUR_API_KEY';
$url = '<?= url('/api/v1/sms/send') ?>';

$data = [
    'to' => '+225XXXXXXXXX',
    'message' => 'Hello from API',
    'sender_id' => 'MyApp'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['success']) {
    echo "SMS sent! Cost: " . $result['data']['cost'];
} else {
    echo "Error: " . $result['message'];
}
?&gt;</code></pre>

                    <hr>

                    <!-- SMS History -->
                    <h5 id="sms-history"><i data-feather="list"></i> SMS History</h5>
                    <div class="mb-3">
                        <span class="badge bg-primary">GET</span>
                        <code><?= url('/api/v1/sms/history') ?></code>
                    </div>

                    <h6>Query Parameters</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td>No</td>
                                <td>Page number (default: 1)</td>
                            </tr>
                            <tr>
                                <td><code>limit</code></td>
                                <td>integer</td>
                                <td>No</td>
                                <td>Results per page (default: 20, max: 100)</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td>string</td>
                                <td>No</td>
                                <td>Filter by status (sent, failed, pending)</td>
                            </tr>
                            <tr>
                                <td><code>from_date</code></td>
                                <td>string</td>
                                <td>No</td>
                                <td>Start date (YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>to_date</code></td>
                                <td>string</td>
                                <td>No</td>
                                <td>End date (YYYY-MM-DD)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Success Response (200 OK)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": [
    {
      "id": 123,
      "recipient": "+225XXXXXXXXX",
      "message": "Hello from API",
      "sender_id": "MyApp",
      "status": "sent",
      "segments": 1,
      "cost": 15.00,
      "gateway": "orange",
      "created_at": "2025-11-29 10:30:00",
      "sent_at": "2025-11-29 10:30:05"
    }
  ],
  "pagination": {
    "total": 150,
    "page": 1,
    "limit": 20,
    "pages": 8
  }
}</code></pre>

                    <h6>Example Request (cURL)</h6>
                    <pre class="bg-light p-3 rounded"><code>curl -X GET "<?= url('/api/v1/sms/history') ?>?page=1&limit=20&status=sent" \
  -H "Authorization: Bearer YOUR_API_KEY"</code></pre>

                    <hr>

                    <!-- Balance -->
                    <h5 id="balance"><i data-feather="dollar-sign"></i> Get Balance</h5>
                    <div class="mb-3">
                        <span class="badge bg-primary">GET</span>
                        <code><?= url('/api/v1/sms/balance') ?></code>
                    </div>

                    <h6>Success Response (200 OK)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "balance": 50000.00,
    "currency": "XOF",
    "statistics": {
      "total_sent": 1234,
      "total_failed": 12,
      "total_cost": 18510.00
    }
  }
}</code></pre>

                    <h6>Example Request (cURL)</h6>
                    <pre class="bg-light p-3 rounded"><code>curl -X GET <?= url('/api/v1/sms/balance') ?> \
  -H "Authorization: Bearer YOUR_API_KEY"</code></pre>

                    <hr>

                    <!-- Error Codes -->
                    <h5 id="error-codes"><i data-feather="alert-triangle"></i> Error Codes</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Error</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>400</td>
                                <td>Bad Request</td>
                                <td>Missing or invalid parameters</td>
                            </tr>
                            <tr>
                                <td>401</td>
                                <td>Unauthorized</td>
                                <td>Invalid or missing API key</td>
                            </tr>
                            <tr>
                                <td>500</td>
                                <td>Server Error</td>
                                <td>Internal server error</td>
                            </tr>
                        </tbody>
                    </table>

                    <hr>

                    <!-- Rate Limits -->
                    <h5 id="rate-limits"><i data-feather="zap"></i> Best Practices</h5>
                    <ul>
                        <li>Always use HTTPS for API requests</li>
                        <li>Keep your API key secure and never share it publicly</li>
                        <li>Use international phone number format (E.164) for best results</li>
                        <li>Handle errors gracefully and implement retry logic for failed requests</li>
                        <li>Monitor your balance regularly to avoid service interruptions</li>
                        <li>Test with small volumes before scaling up</li>
                    </ul>

                    <hr>

                    <div class="alert alert-info">
                        <i data-feather="help-circle"></i> Need help? Contact support or check your <a href="<?= url('/admin/api-keys') ?>">API settings</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
feather.replace();
</script>
@endsection
