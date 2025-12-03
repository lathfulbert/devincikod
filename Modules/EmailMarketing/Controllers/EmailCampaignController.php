<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Models\EmailCampaign;
use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\EmailMarketing\Services\EmailSenderService;
use Modules\EmailMarketing\Services\EmailGatewayFactory;
use Modules\EmailMarketing\Services\CampaignAnalyticsService;

/**
 * Gestion des campagnes email
 */
class EmailCampaignController
{
    protected EmailSenderService $senderService;
    protected CampaignAnalyticsService $analyticsService;

    public function __construct()
    {
        $gatewayFactory = new EmailGatewayFactory();
        $this->senderService = new EmailSenderService($gatewayFactory);
        $this->analyticsService = new CampaignAnalyticsService();
    }

    /**
     * Liste des campagnes
     */
    public function index()
    {
        $status = $_GET['status'] ?? null;

        $query = EmailCampaign::query()->orderBy('created_at', 'DESC');

        if ($status) {
            $query->where('status', $status);
        }

        $campaigns = $query->get();

        echo view('emailmarketing/campaigns/index', [
            'title' => 'Email Campaigns',
            'campaigns' => $campaigns,
            'currentStatus' => $status
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $templates = EmailTemplate::where('is_active', true)->get();

        echo view('emailmarketing/campaigns/create', [
            'title' => 'Create Email Campaign',
            'templates' => $templates
        ]);
    }

    /**
     * Enregistrer une nouvelle campagne
     */
    public function store()
    {
        $data = $_POST;

        // Validation basique
        if (empty($data['name']) || empty($data['subject'])) {
            $_SESSION['flash']['danger'][] = 'Name and subject are required';
            redirect('/admin/email-marketing/campaigns/create');
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;

        $campaign = EmailCampaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'template_id' => $data['template_id'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'from_email' => $data['from_email'] ?? null,
            'reply_to' => $data['reply_to'] ?? null,
            'status' => 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by' => $userId,
            'use_personalization' => isset($data['use_personalization']) ? 1 : 0
        ]);

        $_SESSION['flash']['success'][] = 'Campaign created successfully';
        redirect('/admin/email-marketing/campaigns/' . $campaign->id);
        exit;
    }

    /**
     * Afficher une campagne
     */
    public function show()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        // Statistiques de la campagne
        $stats = $this->analyticsService->getEmailCampaignStats($campaign->id);

        echo view('emailmarketing/campaigns/show', [
            'title' => 'Campaign: ' . $campaign->name,
            'campaign' => $campaign,
            'stats' => $stats
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        $templates = EmailTemplate::where('is_active', true)->get();

        echo view('emailmarketing/campaigns/edit', [
            'title' => 'Edit Campaign: ' . $campaign->name,
            'campaign' => $campaign,
            'templates' => $templates
        ]);
    }

    /**
     * Mettre à jour une campagne
     */
    public function update()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        // Ne peut modifier que les campagnes draft ou scheduled
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            $_SESSION['flash']['danger'][] = 'Cannot edit campaign in current status';
            redirect('/admin/email-marketing/campaigns/' . $campaign->id);
            exit;
        }

        $data = $_POST;

        $campaign->update([
            'name' => $data['name'] ?? $campaign->name,
            'subject' => $data['subject'] ?? $campaign->subject,
            'template_id' => $data['template_id'] ?? $campaign->template_id,
            'from_name' => $data['from_name'] ?? $campaign->from_name,
            'from_email' => $data['from_email'] ?? $campaign->from_email,
            'reply_to' => $data['reply_to'] ?? $campaign->reply_to,
            'scheduled_at' => $data['scheduled_at'] ?? $campaign->scheduled_at,
            'use_personalization' => isset($data['use_personalization']) ? 1 : 0
        ]);

        $_SESSION['flash']['success'][] = 'Campaign updated successfully';
        redirect('/admin/email-marketing/campaigns/' . $campaign->id);
        exit;
    }

    /**
     * Envoyer une campagne
     */
    public function send()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        if ($campaign->status === 'completed') {
            $_SESSION['flash']['danger'][] = 'Campaign already sent';
            redirect('/admin/email-marketing/campaigns/' . $campaign->id);
            exit;
        }

        // Récupérer les contacts (à adapter selon votre logique)
        $contactIds = json_decode($campaign->contact_ids ?? '[]', true);

        if (empty($contactIds)) {
            $_SESSION['flash']['danger'][] = 'No contacts selected for this campaign';
            redirect('/admin/email-marketing/campaigns/' . $campaign->id);
            exit;
        }

        // Charger les contacts depuis la base
        $db = \App\Core\Database\Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($contactIds), '?'));
        $contacts = $db->query(
            "SELECT * FROM contacts WHERE id IN ($placeholders)",
            $contactIds
        )->fetchAll();

        if (empty($contacts)) {
            $_SESSION['flash']['danger'][] = 'No valid contacts found';
            redirect('/admin/email-marketing/campaigns/' . $campaign->id);
            exit;
        }

        // Mettre à jour le total de destinataires
        $campaign->update(['total_recipients' => count($contacts)]);

        try {
            // Envoyer la campagne (en production, utiliser un job queue)
            $result = $this->senderService->sendCampaign($campaign, $contacts);

            if ($result['success']) {
                $_SESSION['flash']['success'][] = "Campaign sent successfully! {$result['results']['sent']}/{$result['results']['total']} emails sent";
            } else {
                $_SESSION['flash']['warning'][] = "Campaign sent with errors. {$result['results']['sent']} sent, {$result['results']['failed']} failed";
            }
        } catch (\Exception $e) {
            $_SESSION['flash']['danger'][] = 'Error sending campaign: ' . $e->getMessage();
        }

        redirect('/admin/email-marketing/campaigns/' . $campaign->id);
        exit;
    }

    /**
     * Mettre en pause une campagne
     */
    public function pause()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        $campaign->update(['status' => 'paused']);

        $_SESSION['flash']['success'][] = 'Campaign paused';
        redirect('/admin/email-marketing/campaigns/' . $campaign->id);
        exit;
    }

    /**
     * Reprendre une campagne
     */
    public function resume()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        $campaign->update(['status' => 'sending']);

        $_SESSION['flash']['success'][] = 'Campaign resumed';
        redirect('/admin/email-marketing/campaigns/' . $campaign->id);
        exit;
    }

    /**
     * Supprimer une campagne
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        // Ne peut supprimer que les campagnes draft
        if ($campaign->status !== 'draft') {
            $_SESSION['flash']['danger'][] = 'Cannot delete campaign in current status';
            redirect('/admin/email-marketing/campaigns/' . $campaign->id);
            exit;
        }

        $campaign->delete();

        $_SESSION['flash']['success'][] = 'Campaign deleted successfully';
        redirect('/admin/email-marketing/campaigns');
        exit;
    }

    /**
     * Analytics d'une campagne
     */
    public function analytics()
    {
        $id = $_GET['id'] ?? null;
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash']['danger'][] = 'Campaign not found';
            redirect('/admin/email-marketing/campaigns');
            exit;
        }

        $stats = $this->analyticsService->getEmailCampaignStats($campaign->id);
        $timeSeriesData = $this->analyticsService->getTimeSeriesData($campaign->id, 'hour');

        echo view('emailmarketing/campaigns/analytics', [
            'title' => 'Campaign Analytics: ' . $campaign->name,
            'campaign' => $campaign,
            'stats' => $stats,
            'timeSeriesData' => $timeSeriesData
        ]);
    }
}
