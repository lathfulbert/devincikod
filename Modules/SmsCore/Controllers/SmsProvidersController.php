<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class SmsProvidersController
{
    /**
     * Affiche la liste des fournisseurs SMS
     */
    public function index()
    {
        $app = Application::getInstance();
        // TODO: Récupérer la liste des fournisseurs
        return view('SmsCore/sms/providers', [
            'title' => 'Fournisseurs SMS'
        ]);
    }
}
