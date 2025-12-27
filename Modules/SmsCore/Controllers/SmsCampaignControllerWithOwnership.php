<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use App\Core\Authorization\Traits\AuthorizesOwnership;
use Modules\SmsCore\Models\SmsCampaign;
use Modules\SmsCore\Models\SmsQueue;
use Modules\SmsCore\Models\SenderName;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Services\FieldPersonalizationService;

/**
 * SmsCampaignController avec contrôle d'accès basé sur la propriété
 *
 * Les admins voient toutes les campagnes
 * Les autres utilisateurs ne voient que leurs propres campagnes
 */
class SmsCampaignControllerWithOwnership
{
    use AuthorizesOwnership;

    protected FieldPersonalizationService $personalizationService;

    public function __construct()
    {
        $this->personalizationService = new FieldPersonalizationService();
        $this->initializeOwnershipPolicy();
    }

    /**
     * List campaigns
     * Admin: toutes les campagnes
     * Autres: seulement leurs campagnes
     */
    public function index()
    {
        $app = Application::getInstance();

        // Créer la requête de base
        $query = SmsCampaign::query()->orderBy('created_at', 'desc');

        // Filtrer par propriétaire si nécessaire (admin voit tout)
        $query = $this->scopeByOwnership($query, 'user_id');

        // Exécuter la requête
        $campaigns = $query->get();

        return view('SmsCore/sms/campaigns/index', [
            'campaigns' => $campaigns,
            'title' => 'SMS Campaigns',
            'isAdmin' => $this->isAdmin()
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

        // Get user's sender names
        $userId = $_SESSION['user']['id'] ?? null;
        $senderNames = $userId ? SenderName::getForUser($userId) : [];

        return view('SmsCore/sms/campaigns/create', [
            'title' => 'Nouvelle Campagne SMS',
            'contacts' => $contacts,
            'placeholders' => $placeholders,
            'senderNames' => $senderNames
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
            $senderNameId = (int)($_POST['sender_name_id'] ?? 0);
            $contactIds = $_POST['contact_ids'] ?? [];
            $userId = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? null;

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

            // Get and verify sender name
            $senderName = null;
            $sender = 'SMS';

            if ($senderNameId) {
                // Verify user has access to this sender name
                if (!SenderName::userHasAccess($userId, $senderNameId)) {
                    flash('error', 'Vous n\'avez pas accès à ce Sender Name');
                    redirect('/admin/sms/campaigns/create');
                    return;
                }

                $senderName = SenderName::find($senderNameId);
                if ($senderName) {
                    $sender = $senderName->name;
                }
            }

            // Create campaign (user_id sera automatiquement rempli)
            $campaign = SmsCampaign::create([
                'name' => $name,
                'message' => $message,
                'sender' => $sender,
                'user_id' => $userId, // Propriétaire de la campagne
                'status' => 'pending',
                'total_recipients' => count($contactIds),
                'sent_count' => 0,
                'failed_count' => 0
            ]);

            // Queue messages for each contact
            foreach ($contactIds as $contactId) {
                $contact = Contact::find($contactId);
                if (!$contact) continue;

                $personalizedMessage = $this->personalizationService->personalize($message, $contact);

                SmsQueue::create([
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->id,
                    'to' => $contact->phone,
                    'from' => $sender,
                    'message' => $personalizedMessage,
                    'user_id' => $userId,
                    'status' => 'pending'
                ]);
            }

            flash('success', 'Campagne créée avec succès. ' . count($contactIds) . ' messages en attente.');
            redirect('/admin/sms/campaigns');

        } catch (\Exception $e) {
            flash('error', 'Erreur lors de la création : ' . $e->getMessage());
            redirect('/admin/sms/campaigns/create');
        }
    }

    /**
     * Show campaign details
     */
    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        // Vérifier l'autorisation de voir cette campagne
        $this->authorizeView($campaign, 'user_id', '/admin/sms/campaigns');

        // Get campaign messages
        $messages = SmsQueue::where('campaign_id', $id)->orderBy('created_at', 'desc')->get();

        return view('SmsCore/sms/campaigns/show', [
            'title' => 'Détails de la campagne',
            'campaign' => $campaign,
            'messages' => $messages,
            'canEdit' => $this->canUpdate($campaign, 'user_id'),
            'canDelete' => $this->canDelete($campaign, 'user_id')
        ]);
    }

    /**
     * Show edit campaign form
     */
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        // Vérifier l'autorisation de modifier
        $this->authorizeUpdate($campaign, 'user_id', '/admin/sms/campaigns');

        $contacts = Contact::query()->where('is_active', 1)->orderBy('first_name')->get();
        $placeholders = $this->personalizationService->getAvailablePlaceholders();

        // Get user's sender names
        $userId = $_SESSION['user']['id'] ?? null;
        $senderNames = $userId ? SenderName::getForUser($userId) : [];

        return view('SmsCore/sms/campaigns/edit', [
            'title' => 'Modifier la campagne',
            'campaign' => $campaign,
            'contacts' => $contacts,
            'placeholders' => $placeholders,
            'senderNames' => $senderNames
        ]);
    }

    /**
     * Update campaign
     */
    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        // Vérifier l'autorisation de modifier
        $this->authorizeUpdate($campaign, 'user_id', '/admin/sms/campaigns');

        try {
            $campaign->name = sanitize($_POST['name'] ?? '', 'string');
            $campaign->message = sanitize($_POST['message'] ?? '', 'string');
            $campaign->save();

            flash('success', 'Campagne modifiée avec succès');
            redirect('/admin/sms/campaigns/show?id=' . $campaign->id);

        } catch (\Exception $e) {
            flash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            redirect('/admin/sms/campaigns/edit?id=' . $id);
        }
    }

    /**
     * Delete campaign
     */
    public function delete()
    {
        $id = (int)($_POST['id'] ?? 0);
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        // Vérifier l'autorisation de supprimer
        $this->authorizeDelete($campaign, 'user_id', '/admin/sms/campaigns');

        try {
            // Delete related queue messages
            SmsQueue::where('campaign_id', $id)->delete();

            // Delete campaign
            $campaign->delete();

            flash('success', 'Campagne supprimée avec succès');
            redirect('/admin/sms/campaigns');

        } catch (\Exception $e) {
            flash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            redirect('/admin/sms/campaigns');
        }
    }

    /**
     * Start campaign (send all pending messages)
     */
    public function start()
    {
        $id = (int)($_POST['id'] ?? 0);
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        // Vérifier l'autorisation
        $this->authorizeUpdate($campaign, 'user_id', '/admin/sms/campaigns');

        try {
            // Mark campaign as running
            $campaign->status = 'running';
            $campaign->started_at = date('Y-m-d H:i:s');
            $campaign->save();

            // Process queue (would be done by a background job in production)
            // For now, just update status
            flash('success', 'Campagne démarrée. Les messages seront envoyés par le processus de queue.');
            redirect('/admin/sms/campaigns/show?id=' . $campaign->id);

        } catch (\Exception $e) {
            flash('error', 'Erreur lors du démarrage : ' . $e->getMessage());
            redirect('/admin/sms/campaigns/show?id=' . $id);
        }
    }
}
