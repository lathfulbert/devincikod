<?php

namespace Modules\WhatsAppMarketing\Controllers;

use App\Core\Application;
use Modules\WhatsAppMarketing\Models\WhatsAppGateway;

class WhatsAppGatewayController
{
    public function index()
    {
        $gateways = WhatsAppGateway::all();
        return view('WhatsAppMarketing/gateways/index', [
            'title' => 'Configuration des Passerelles WhatsApp',
            'gateways' => $gateways
        ]);
    }

    public function create()
    {
        return view('WhatsAppMarketing/gateways/create', [
            'title' => 'Nouvelle Passerelle WhatsApp'
        ]);
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $provider = $_POST['provider'] ?? '';

        // Credentials building
        $credentials = [];
        if ($provider === 'twilio') {
            $credentials = [
                'sid' => $_POST['twilio_sid'] ?? '',
                'token' => $_POST['twilio_token'] ?? '',
                'from_number' => $_POST['phone_number'] ?? ''
            ];
        } else {
            // Generic api key for mock/other
            $credentials = ['api_key' => $_POST['api_key'] ?? ''];
        }

        $gateway = new WhatsAppGateway();
        $gateway->name = $name;
        $gateway->provider = $provider;
        $gateway->phone_number = $_POST['phone_number'] ?? '';
        $gateway->credentials = json_encode($credentials);
        $gateway->is_active = 1;
        $gateway->save();

        flash('success', 'Passerelle créée avec succès');
        redirect('/admin/whatsapp/gateways');
    }

    public function delete($id)
    {
        $gateway = WhatsAppGateway::find($id);
        if ($gateway) {
            $gateway->delete();
            flash('success', 'Passerelle supprimée');
        }
        redirect('/admin/whatsapp/gateways');
    }
}
