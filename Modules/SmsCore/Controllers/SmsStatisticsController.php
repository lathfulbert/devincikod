<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;

class SmsStatisticsController
{
    /**
     * Affiche les statistiques SMS
     */
    public function index()
    {
        $app = Application::getInstance();
        // TODO: Récupérer les statistiques SMS
        return view('SmsCore/sms/statistics', [
            'title' => 'Statistiques SMS'
        ]);
    }
}
