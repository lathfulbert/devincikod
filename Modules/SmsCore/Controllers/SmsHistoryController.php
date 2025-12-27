<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class SmsHistoryController
{
    /**
     * Affiche l'historique des SMS
     */
    public function index()
    {
        $app = Application::getInstance();
        // TODO: Récupérer l'historique des SMS
        return view('SmsCore/sms/history', [
            'title' => 'Historique des SMS'
        ]);
    }
}
