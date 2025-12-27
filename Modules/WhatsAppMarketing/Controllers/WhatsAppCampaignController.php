<?php

namespace Modules\WhatsAppMarketing\Controllers;

use App\Core\Application;
use Modules\WhatsAppMarketing\Models\WhatsAppCampaign;
use Modules\WhatsAppMarketing\Models\WhatsAppTemplate;

class WhatsAppCampaignController
{
    public function index()
    {
        $campaigns = WhatsAppCampaign::all();
        return view('WhatsAppMarketing/campaigns/index', [
            'title' => 'Campagnes WhatsApp',
            'campaigns' => $campaigns
        ]);
    }

    public function create()
    {
        $templates = WhatsAppTemplate::where('status', 'APPROVED')->get();
        return view('WhatsAppMarketing/campaigns/create', [
            'title' => 'Nouvelle Campagne',
            'templates' => $templates
        ]);
    }

    public function store()
    {
        try {
            $name = $_POST['name'] ?? null;
            $templateId = $_POST['template_id'] ?? null;
            $audienceType = $_POST['audience_type'] ?? 'all';
            $scheduledAt = $_POST['scheduled_at'] ?? null;

            if (!$name || !$templateId) {
                flash('error', 'Nom et Template requis.');
                redirect('/admin/whatsapp/campaigns/create');
                return;
            }

            // Create Campaign
            $campaign = new WhatsAppCampaign();
            $campaign->name = $name;
            $campaign->template_id = $templateId;
            $campaign->audiences = json_encode(['type' => $audienceType]);
            $campaign->scheduled_at = $scheduledAt ?: date('Y-m-d H:i:s');
            $campaign->status = $scheduledAt ? 'scheduled' : 'processing';
            $campaign->save();

            // Fetch Template details
            $template = WhatsAppTemplate::find($templateId);
            $components = json_decode($template->components, true) ?? [];

            // Get Audience
            if ($audienceType === 'all') {
                // Fetch all contacts (simplified for now, ideally strictly opt-in)
                $contacts = \Modules\Contacts\Models\Contact::where('is_active', 1)->get();
            } else {
                // Placeholder for segments
                $contacts = [];
            }

            $campaign->total_recipients = count($contacts);
            $campaign->save();

            // If immediate execution
            if (!$scheduledAt) {
                $service = new \Modules\WhatsAppMarketing\Services\WhatsAppService();
                $sentCount = 0;
                $failCount = 0;

                foreach ($contacts as $contact) {
                    if (empty($contact->phone)) continue;

                    // Send Template
                    $result = $service->sendTemplate(
                        $contact->phone,
                        $template->name,
                        $template->language,
                        $components,
                        $campaign->id
                    );

                    if ($result['success']) {
                        $sentCount++;
                    } else {
                        $failCount++;
                    }
                }

                $campaign->total_sent = $sentCount;
                $campaign->total_failed = $failCount;
                $campaign->status = 'completed';
                $campaign->save();

                flash('success', "Campagne envoyée: $sentCount succès, $failCount échecs.");
            } else {
                flash('success', "Campagne planifiée pour $scheduledAt.");
            }

            redirect('/admin/whatsapp/campaigns');
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/whatsapp/campaigns/create');
        }
    }
}
