<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Models\EmailCampaign;
use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\EmailMarketing\Models\Workflow;
use Modules\EmailMarketing\Services\EmailSenderService;
use Modules\EmailMarketing\Services\EmailGatewayFactory;
use Modules\EmailMarketing\Services\MultiChannelService;
use Modules\EmailMarketing\Services\CampaignAnalyticsService;

/**
 * API RESTful pour Email Marketing
 */
class EmailApiController
{
    protected EmailSenderService $senderService;
    protected MultiChannelService $multiChannelService;
    protected CampaignAnalyticsService $analyticsService;

    public function __construct()
    {
        $gatewayFactory = new EmailGatewayFactory();
        $this->senderService = new EmailSenderService($gatewayFactory);
        $this->multiChannelService = new MultiChannelService($this->senderService);
        $this->analyticsService = new CampaignAnalyticsService();
    }

    /**
     * Envoyer un email unique via API
     * POST /api/v1/email/send
     */
    public function send()
    {
        header('Content-Type: application/json');

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Validation
        if (empty($data['to']) || empty($data['subject']) || empty($data['html'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required fields: to, subject, html'
            ]);
            exit;
        }

        try {
            $result = $this->senderService->send(
                $data['to'],
                $data['subject'],
                $data['html'],
                [
                    'from' => $data['from'] ?? null,
                    'from_name' => $data['from_name'] ?? null,
                    'reply_to' => $data['reply_to'] ?? null,
                    'user_id' => $data['user_id'] ?? null,
                    'metadata' => $data['metadata'] ?? []
                ]
            );

            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Envoyer des emails en masse via API
     * POST /api/v1/email/send-bulk
     */
    public function sendBulk()
    {
        header('Content-Type: application/json');

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Validation
        if (empty($data['recipients']) || empty($data['template_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required fields: recipients, template_id'
            ]);
            exit;
        }

        try {
            $result = $this->senderService->sendBulk(
                $data['recipients'],
                $data['template_id'],
                $data['options'] ?? []
            );

            http_response_code($result['success'] ? 200 : 207); // 207 = Multi-Status
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Liste des campagnes via API
     * GET /api/v1/email/campaigns
     */
    public function campaigns()
    {
        header('Content-Type: application/json');

        $status = $_GET['status'] ?? null;
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        $query = EmailCampaign::query()->orderBy('created_at', 'DESC');

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $campaigns = $query->limit($limit)->offset($offset)->get();

        echo json_encode([
            'success' => true,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'data' => $campaigns->toArray()
        ]);
        exit;
    }

    /**
     * Détails d'une campagne via API
     * GET /api/v1/email/campaigns/{id}
     */
    public function getCampaign()
    {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Campaign not found'
            ]);
            exit;
        }

        $stats = $this->analyticsService->getEmailCampaignStats($campaign->id);

        echo json_encode([
            'success' => true,
            'data' => [
                'campaign' => $campaign->toArray(),
                'stats' => $stats
            ]
        ]);
        exit;
    }

    /**
     * Statistiques via API
     * GET /api/v1/email/stats
     */
    public function stats()
    {
        header('Content-Type: application/json');

        $period = $_GET['period'] ?? 'month';

        $filters = [];
        switch ($period) {
            case 'today':
                $filters['start_date'] = date('Y-m-d');
                break;
            case 'week':
                $filters['start_date'] = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'month':
                $filters['start_date'] = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'year':
                $filters['start_date'] = date('Y-m-d', strtotime('-365 days'));
                break;
        }

        $filters['end_date'] = date('Y-m-d');

        $stats = $this->analyticsService->getGlobalEmailStats($filters);

        echo json_encode([
            'success' => true,
            'period' => $period,
            'data' => $stats
        ]);
        exit;
    }

    /**
     * Déclencher un workflow via API
     * POST /api/v1/workflow/trigger
     */
    public function triggerWorkflow()
    {
        header('Content-Type: application/json');

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Validation
        if (empty($data['workflow_id']) || empty($data['contact_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required fields: workflow_id, contact_id'
            ]);
            exit;
        }

        $workflow = Workflow::find($data['workflow_id']);

        if (!$workflow) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Workflow not found'
            ]);
            exit;
        }

        // Charger le contact
        $db = \App\Core\Database\Database::getInstance();
        $contact = $db->query(
            "SELECT * FROM contacts WHERE id = ?",
            [$data['contact_id']]
        )->fetch();

        if (!$contact) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Contact not found'
            ]);
            exit;
        }

        try {
            $result = $this->multiChannelService->executeWorkflow($workflow, $contact);

            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
