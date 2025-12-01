<?php

namespace Modules\SmsCore\Controllers;

use Modules\Settings\Models\SmsGateway;
use Modules\SmsCore\Services\SmsGatewayFactory;
use Modules\SmsCore\Services\SmsPricingService;
use Modules\SmsCore\Services\SmsBillingService;
use Modules\SmsCore\Services\SmsSenderService;
use Modules\SmsCore\Models\SmsBillingLog;
use Modules\Wallet\Services\WalletService;

class SmsApiController
{
    /**
     * Authenticate API request
     */
    private function authenticate()
    {
        $apiKey = $this->extractApiKey();

        if (!$apiKey) {
            $this->sendUnauthorizedResponse('API key required');
            return null;
        }

        // Find user by API key
        $user = \Modules\Users\Models\User::where('api_key', $apiKey)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            $this->sendUnauthorizedResponse('Invalid API key');
            return null;
        }

        return $user;
    }

    /**
     * Extract API key from request
     */
    private function extractApiKey(): ?string
    {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.+)/', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Check query parameter
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        // Check POST data
        if (isset($_POST['api_key'])) {
            return $_POST['api_key'];
        }

        // Check JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['api_key'])) {
            return $input['api_key'];
        }

        return null;
    }

    /**
     * Send unauthorized response
     */
    private function sendUnauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => $message
        ]);
        exit;
    }

    /**
     * Send SMS via API
     * POST /api/v1/sms/send
     *
     * Parameters:
     * - to: string (phone number)
     * - message: string (SMS content)
     * - sender_id: string (optional, sender ID)
     */
    public function send()
    {
        header('Content-Type: application/json');

        // Authenticate
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $to = $input['to'] ?? '';
        $message = $input['message'] ?? '';
        $senderId = $input['sender_id'] ?? null;

        // Validation
        if (empty($to) || empty($message)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Bad Request',
                'message' => 'Missing required fields: to, message'
            ]);
            exit;
        }

        // Clean phone number
        $to = preg_replace('/[^0-9+]/', '', $to);

        if (strlen($message) > 1600) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Bad Request',
                'message' => 'Message too long (max 1600 characters)'
            ]);
            exit;
        }

        try {
            // Get default gateway
            $gatewayConfig = SmsGateway::getDefault();

            if (!$gatewayConfig) {
                throw new \Exception('No SMS gateway configured');
            }

            // Create gateway instance
            $gateway = SmsGatewayFactory::create($gatewayConfig);

            if (!$gateway) {
                throw new \Exception('Failed to create gateway instance');
            }

            // Initialize services
            $pricingService = new SmsPricingService();
            $billingService = new SmsBillingService();
            $senderService = new SmsSenderService($gateway, $pricingService, $billingService);

            // Send SMS
            $result = $senderService->send(
                $to,
                $message,
                $senderId,
                [
                    'user_id' => $user->id,
                    'gateway_name' => $gatewayConfig->provider_code,
                    'source' => 'api'
                ]
            );

            if ($result['success']) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => [
                        'message_id' => $result['message_id'] ?? null,
                        'recipient' => $to,
                        'segments' => $result['segments'] ?? 1,
                        'cost' => $result['cost'] ?? 0,
                        'currency' => $result['currency'] ?? 'XOF'
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Send Failed',
                    'message' => $result['message'] ?? 'Failed to send SMS'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Get SMS history
     * GET /api/v1/sms/history
     *
     * Parameters:
     * - page: int (optional, default 1)
     * - limit: int (optional, default 20, max 100)
     * - status: string (optional, filter by status)
     * - from_date: string (optional, YYYY-MM-DD)
     * - to_date: string (optional, YYYY-MM-DD)
     */
    public function history()
    {
        header('Content-Type: application/json');

        // Authenticate
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        // Get query parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        $status = $_GET['status'] ?? null;
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;

        $offset = ($page - 1) * $limit;

        try {
            // Build query
            $query = SmsBillingLog::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($fromDate) {
                $query->where('created_at', '>=', $fromDate . ' 00:00:00');
            }

            if ($toDate) {
                $query->where('created_at', '<=', $toDate . ' 23:59:59');
            }

            // Get total count
            $total = $query->count();

            // Get paginated results
            $logs = $query->limit($limit)->offset($offset)->get();

            // Format results
            $data = [];
            foreach ($logs as $log) {
                $data[] = [
                    'id' => $log->id ?? null,
                    'recipient' => $log->recipient ?? '',
                    'sender_id' => $log->sender_id ?? '',
                    'status' => $log->status ?? '',
                    'segments' => $log->segments ?? 1,
                    'cost' => $log->total_cost ?? 0,
                    'currency' => $log->currency ?? 'XOF',
                    'gateway' => $log->gateway ?? '',
                    'country_code' => $log->country_code ?? '',
                    'operator' => $log->operator ?? '',
                    'created_at' => $log->created_at ?? null
                ];
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Get SMS balance
     * GET /api/v1/sms/balance
     *
     * Returns the user's wallet balance
     */
    public function balance()
    {
        header('Content-Type: application/json');

        // Authenticate
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        try {
            $walletService = new WalletService();
            $balance = $walletService->getBalance($user->id);

            // Get SMS stats
            $totalSent = SmsBillingLog::where('user_id', $user->id)
                ->where('status', 'paid')
                ->count();

            $totalFailed = SmsBillingLog::where('user_id', $user->id)
                ->where('status', 'failed')
                ->count();

            // Calculate total cost using raw query
            $db = \App\Core\Database\Database::getInstance();
            $costResult = $db->query("SELECT SUM(total_cost) as total FROM sms_billing_logs WHERE user_id = ? AND status = 'paid'", [$user->id])->fetch();
            $totalCost = $costResult['total'] ?? 0;

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'balance' => $balance,
                    'currency' => 'XOF',
                    'statistics' => [
                        'total_sent' => $totalSent,
                        'total_failed' => $totalFailed,
                        'total_cost' => $totalCost
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ]);
        }

        exit;
    }
}
