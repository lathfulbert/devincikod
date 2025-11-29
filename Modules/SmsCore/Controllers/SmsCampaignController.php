<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\SmsCore\Models\SmsCampaign;
use Modules\SmsCore\Models\SmsQueue;

class SmsCampaignController
{
    /**
     * List all campaigns
     */
    public function index()
    {
        $app = Application::getInstance();

        $campaigns = SmsCampaign::orderBy('created_at', 'desc')->get();

        echo $app->view->render('backend/sms/campaigns/index', [
            'campaigns' => $campaigns,
            'title' => 'SMS Campaigns'
        ]);
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

        echo $app->view->render('backend/sms/campaigns/show', [
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
