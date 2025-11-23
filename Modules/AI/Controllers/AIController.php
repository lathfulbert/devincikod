<?php

namespace Modules\AI\Controllers;

use App\Core\Application;
use App\Core\AI\AIManager;

class AIController
{
    public function index()
    {
        $app = Application::getInstance();
        $agents = AIManager::getInstance()->getAllAgents();

        echo $app->view->render('backend/ai/index', [
            'title' => 'Gestion AI',
            'agents' => $agents
        ]);
    }

    public function settings()
    {
        $app = Application::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Save settings logic here (e.g., to .env or database)
            // For now, just flash a message
            $_SESSION['flash']['success'] = 'Paramètres enregistrés (simulation)';
        }

        echo $app->view->render('backend/ai/settings', [
            'title' => 'Configuration AI'
        ]);
    }

    public function logs()
    {
        $app = Application::getInstance();
        // Placeholder for logs
        $logs = [];

        echo $app->view->render('backend/ai/logs', [
            'title' => 'Historique AI',
            'logs' => $logs
        ]);
    }

    public function test()
    {
        $app = Application::getInstance();
        $response = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $prompt = $_POST['prompt'] ?? '';
            $manager = AIManager::getInstance();
            $client = $manager->getOpenAIClient();

            if ($client) {
                try {
                    $result = $client->chat()->create([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);
                    $response = $result->choices[0]->message->content;
                    $_SESSION['flash']['success'] = 'Réponse reçue !';
                } catch (\Exception $e) {
                    $_SESSION['flash']['error'] = 'Erreur : ' . $e->getMessage();
                }
            } else {
                $_SESSION['flash']['error'] = 'Client OpenAI non initialisé. Vérifiez la clé API.';
            }
        }

        echo $app->view->render('backend/ai/test', [
            'title' => 'Test AI',
            'response' => $response
        ]);
    }
}
