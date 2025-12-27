<?php

namespace Modules\WhatsAppMarketing\Controllers;

use App\Core\Application;
use Modules\WhatsAppMarketing\Models\WhatsAppMessage;

class WhatsAppMessageController
{
    public function index()
    {
        // Simple paginated list placeholder
        $messages = WhatsAppMessage::query()->orderBy('created_at', 'desc')->limit(50)->get();
        // create view placeholder if needed or just dump for now
        // return view('whatsapp/messages/index' ...

        // For now, let's redirect to dashboard or show a "Coming Soon"
        flash('info', 'Historique des messages détaillé bientôt disponible.');
        redirect('/admin/whatsapp');
    }
}
