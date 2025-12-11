    /**
     * SMS Statistics Page with Real Data
     * Route: /admin/sms/statistics
     */
    public function statistics()
    {
        $app = Application::getInstance();

        // Get date range from request
        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to = $_GET['to'] ?? date('Y-m-d');

        // Get current user info
        $userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $isAdmin = in_array($userRole, ['admin', 'superadmin']);

        // Query builder for sms_messages
        $queryBuilder = \Modules\SmsCore\Models\SmsMessage::query();

        // Filter by user if not admin
        if (!$isAdmin && $userId) {
            $queryBuilder->where('user_id', $userId);
        }

        // Apply date range filter
        if ($from) {
            $queryBuilder->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $queryBuilder->where('created_at', '<=', $to . ' 23:59:59');
        }

        // Get all messages as array (not collection)
        $allMessages = $queryBuilder->get();

        // Calculate statistics using array functions (not collection methods)
        $totalSent = count($allMessages);

        $totalDelivered = count(array_filter($allMessages, function($msg) {
            return in_array($msg->status, ['sent', 'delivered']);
        }));

        $totalFailed = count(array_filter($allMessages, function($msg) {
            return $msg->status === 'failed';
        }));

        $totalPending = count(array_filter($allMessages, function($msg) {
            return $msg->status === 'pending';
        }));

        // Calculate total cost
        $totalCost = 0;
        foreach ($allMessages as $msg) {
            $totalCost += floatval($msg->cost ?? 0);
        }

        // Group by date for chart (last 30 days)
        $dailyStats = [];
        $dateLabels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dateLabels[] = date('d M', strtotime($date));

            $dailyMessages = array_filter($allMessages, function($msg) use ($date) {
                return date('Y-m-d', strtotime($msg->created_at)) === $date;
            });

            $dailyStats[$date] = [
                'sent' => count(array_filter($dailyMessages, function($m) {
                    return in_array($m->status, ['sent', 'delivered']);
                })),
                'delivered' => count(array_filter($dailyMessages, function($m) {
                    return $m->status === 'delivered';
                })),
                'failed' => count(array_filter($dailyMessages, function($m) {
                    return $m->status === 'failed';
                })),
            ];
        }

        // Prepare chart data
        $sentData = [];
        $deliveredData = [];
        $failedData = [];

        foreach ($dailyStats as $stats) {
            $sentData[] = $stats['sent'];
            $deliveredData[] = $stats['delivered'];
            $failedData[] = $stats['failed'];
        }

        $chartData = [
            'labels' => $dateLabels,
            'sent' => $sentData,
            'delivered' => $deliveredData,
            'failed' => $failedData
        ];

        // Top operators (by message count)
        $operatorStats = [];
        foreach ($allMessages as $msg) {
            $phone = $msg->to ?? '';

            // Detect operator based on phone prefix (Côte d'Ivoire example)
            $operator = 'Autre';
            if (preg_match('/^(\+225)?0?7/', $phone)) {
                $operator = 'Orange';
            } elseif (preg_match('/^(\+225)?0?[45]/', $phone)) {
                $operator = 'MTN';
            } elseif (preg_match('/^(\+225)?0?6/', $phone)) {
                $operator = 'Moov';
            }

            if (!isset($operatorStats[$operator])) {
                $operatorStats[$operator] = 0;
            }
            $operatorStats[$operator]++;
        }

        arsort($operatorStats);

        // Top gateways
        $gatewayStats = [];
        foreach ($allMessages as $msg) {
            $gateway = $msg->gateway ?? 'Unknown';
            if (!isset($gatewayStats[$gateway])) {
                $gatewayStats[$gateway] = 0;
            }
            $gatewayStats[$gateway]++;
        }

        arsort($gatewayStats);

        // Success rate
        $successRate = $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 2) : 0;

        echo view('SmsCore/sms/statistics', [
            'chartData' => $chartData,
            'from' => $from,
            'to' => $to,
            'title' => 'SMS Statistics',
            'totalSent' => $totalSent,
            'totalDelivered' => $totalDelivered,
            'totalFailed' => $totalFailed,
            'totalPending' => $totalPending,
            'totalCost' => $totalCost,
            'successRate' => $successRate,
            'operatorStats' => $operatorStats,
            'gatewayStats' => $gatewayStats,
            'isAdmin' => $isAdmin
        ]);
    }
