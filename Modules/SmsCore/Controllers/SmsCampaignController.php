<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\SmsCore\Models\SmsCampaign;
use Modules\SmsCore\Models\SmsQueue;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Services\FieldPersonalizationService;

class SmsCampaignController
{
    protected FieldPersonalizationService $personalizationService;

    public function __construct()
    {
        $this->personalizationService = new FieldPersonalizationService();
    }

    /**
     * List all campaigns
     */
    public function index()
    {
        $app = Application::getInstance();

        $campaigns = SmsCampaign::orderBy('created_at', 'desc')->get();

        echo view('smscore/sms/campaigns/index', [
            'campaigns' => $campaigns,
            'title' => 'SMS Campaigns'
        ]);
    }

    /**
     * Show create campaign form
     */
    public function create()
    {
        $app = Application::getInstance();

        $contacts = Contact::query()->where('is_active', 1)->orderBy('first_name')->get();
        $placeholders = $this->personalizationService->getAvailablePlaceholders();

        echo view('smscore/sms/campaigns/create', [
            'title' => 'Nouvelle Campagne SMS',
            'contacts' => $contacts,
            'placeholders' => $placeholders
        ]);
    }

    /**
     * Store new campaign
     */
    public function store()
    {
        try {
            $name = sanitize($_POST['name'] ?? '', 'string');
            $message = sanitize($_POST['message'] ?? '', 'string');
            $contactIds = $_POST['contact_ids'] ?? [];

            if (empty($name) || empty($message)) {
                flash('error', 'Le nom et le message sont requis');
                redirect('/admin/sms/campaigns/create');
                return;
            }

            if (empty($contactIds)) {
                flash('error', 'Veuillez sélectionner au moins un contact');
                redirect('/admin/sms/campaigns/create');
                return;
            }

            // Create campaign
            $campaign = new SmsCampaign();
            $campaign->name = $name;
            $campaign->message = $message;
            $campaign->contact_ids = json_encode($contactIds);
            $campaign->use_personalization = isset($_POST['use_personalization']) ? 1 : 0;
            $campaign->status = 'pending';
            $campaign->total_recipients = count($contactIds);
            $campaign->sent_count = 0;
            $campaign->failed_count = 0;
            $campaign->created_by = $_SESSION['user_id'] ?? null;
            $campaign->save();

            // Queue messages for each contact
            foreach ($contactIds as $contactId) {
                $contact = Contact::find($contactId);
                if (!$contact) continue;

                // Personalize message if enabled
                $personalizedMessage = $campaign->use_personalization
                    ? $this->personalizationService->personalize($message, $contact)
                    : $message;

                // Create queue item
                $queueItem = new SmsQueue();
                $queueItem->campaign_id = $campaign->id;
                $queueItem->recipient = $contact->phone;
                $queueItem->message = $personalizedMessage;
                $queueItem->status = 'pending';
                $queueItem->save();
            }

            flash('success', 'Campagne créée avec succès ! ' . count($contactIds) . ' messages en attente.');
            redirect('/admin/sms/campaigns/' . $campaign->id);
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/sms/campaigns/create');
        }
    }

    /**
     * Show edit campaign form
     */
    public function edit($id)
    {
        $app = Application::getInstance();

        $campaign = SmsCampaign::find($id);
        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        $contacts = Contact::query()->where('is_active', 1)->orderBy('first_name')->get();
        $placeholders = $this->personalizationService->getAvailablePlaceholders();
        $selectedContactIds = json_decode($campaign->contact_ids ?? '[]', true);

        echo view('smscore/sms/campaigns/edit', [
            'title' => 'Modifier Campagne: ' . $campaign->name,
            'campaign' => $campaign,
            'contacts' => $contacts,
            'placeholders' => $placeholders,
            'selectedContactIds' => $selectedContactIds
        ]);
    }

    /**
     * Update campaign
     */
    public function update($id)
    {
        $campaign = SmsCampaign::find($id);
        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        try {
            $campaign->name = sanitize($_POST['name'] ?? '', 'string');
            $campaign->message = sanitize($_POST['message'] ?? '', 'string');
            $campaign->use_personalization = isset($_POST['use_personalization']) ? 1 : 0;
            $campaign->save();

            flash('success', 'Campagne mise à jour avec succès');
            redirect('/admin/sms/campaigns/' . $id);
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/sms/campaigns/' . $id . '/edit');
        }
    }

    /**
     * Preview personalized message for a contact (AJAX)
     */
    public function preview()
    {
        header('Content-Type: application/json');

        $message = $_POST['message'] ?? '';
        $contactId = $_POST['contact_id'] ?? null;

        if (!$message || !$contactId) {
            echo json_encode(['error' => 'Message et contact requis']);
            exit;
        }

        $contact = Contact::find($contactId);
        if (!$contact) {
            echo json_encode(['error' => 'Contact introuvable']);
            exit;
        }

        $personalizedMessage = $this->personalizationService->personalize($message, $contact);

        echo json_encode([
            'success' => true,
            'preview' => $personalizedMessage,
            'contact_name' => $contact->getFullName()
        ]);
        exit;
    }

    /**
     * View campaign details
     */
    public function show($id)
    {
        $app = Application::getInstance();

        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash_error'] = 'Campagne introuvable.';
            redirect('/admin/sms/campaigns');
            exit;
        }

        // Get queue items for this campaign
        $queueItems = SmsQueue::where('campaign_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        echo view('smscore/sms/campaigns/show', [
            'campaign' => $campaign,
            'queueItems' => $queueItems,
            'title' => 'Campaign: ' . $campaign->name
        ]);
    }

    /**
     * Delete campaign
     */
    public function delete($id)
    {
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            $_SESSION['flash_error'] = 'Campagne introuvable.';
            redirect('/admin/sms/campaigns');
            exit;
        }

        // Delete associated queue items
        $pdo = \App\Core\Database\Database::getInstance()->getPdo();
        $pdo->exec("DELETE FROM sms_queue WHERE campaign_id = $id");

        // Delete campaign
        $pdo->exec("DELETE FROM sms_campaigns WHERE id = $id");

        $_SESSION['flash_success'] = 'Campagne supprimée avec succès.';
        redirect('/admin/sms/campaigns');
    }
}
