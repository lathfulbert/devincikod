<?php

namespace Modules\WhatsAppMarketing\Controllers;

use App\Core\Application;
use Modules\WhatsAppMarketing\Models\WhatsAppTemplate;
use Modules\WhatsAppMarketing\Models\WhatsAppGateway;
use Modules\WhatsAppMarketing\Services\WhatsAppGatewayFactory;

class WhatsAppTemplateController
{
    public function index()
    {
        $templates = WhatsAppTemplate::all();
        return view('WhatsAppMarketing/templates/index', [
            'title' => 'Modèles de Messages (Templates)',
            'templates' => $templates
        ]);
    }

    public function sync($id)
    {
        // Logic to fetch from provider and update DB

        $gateway = WhatsAppGateway::find($id);
        if (!$gateway) {
            flash('error', 'Passerelle introuvable');
            redirect('/admin/whatsapp/templates');
            return;
        }

        try {
            $provider = WhatsAppGatewayFactory::create($gateway);
            $remoteTemplates = $provider->getTemplates();

            foreach ($remoteTemplates as $rt) {
                // Check if exists
                $tpl = WhatsAppTemplate::where('gateway_id', $gateway->id)
                    ->where('name', $rt['name'])
                    ->where('language', $rt['language'])
                    ->first();

                if (!$tpl) {
                    $tpl = new WhatsAppTemplate();
                    $tpl->gateway_id = $gateway->id;
                    $tpl->name = $rt['name']; // No sanitization needed here usually as it comes from provider
                    $tpl->language = $rt['language'];
                }

                $tpl->category = $rt['category'];
                $tpl->status = $rt['status'];
                $tpl->external_id = $rt['id'] ?? null;
                $tpl->components = json_encode($rt['components'] ?? []);
                $tpl->save();
            }

            flash('success', 'Templates synchronisés avec succès');
        } catch (\Exception $e) {
            flash('error', 'Erreur de synchro: ' . $e->getMessage());
        }

        redirect('/admin/whatsapp/templates');
    }
}
