<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class SmsContractsController
{
    /**
     * Affiche les contrats SMS
     */
    public function index()
    {
        $app = Application::getInstance();
        // TODO: Récupérer les contrats SMS
        return view('SmsCore/sms/contracts', [
            'title' => 'Contrats SMS'
        ]);
    }
}
