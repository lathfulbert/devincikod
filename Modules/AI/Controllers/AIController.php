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

        echo view('AI/ai/index', [
            'title' => 'Gestion AI',
            'agents' => $agents
        ]);
    }

    public function settings()
    {
        $app = Application::getInstance();
        $registry = $app->moduleManager->getRegistry();
        $settings = $registry->getSettings('AI');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $defaultModel = $_POST['default_model'] ?? 'gpt-3.5-turbo';
                $apiKey = $_POST['openai_api_key'] ?? '';

                // Save model to module settings
                $settings['default_model'] = $defaultModel;
                $registry->updateSettings('AI', $settings);

                // Update .env for API Key if provided and different
                if (!empty($apiKey) && $apiKey !== '****************') {
                    $this->updateEnv('OPENAI_API_KEY', $apiKey);

                    // Reinitialize AIManager with new key
                    $manager = \App\Core\AI\AIManager::getInstance();
                    // Force recreation on next request
                }

                $_SESSION['flash']['success'] = 'Paramètres enregistrés avec succès. Modèle: ' . $defaultModel;

                // Redirect to avoid form resubmission
                header('Location: ' . url('/admin/ai/settings'));
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash']['danger'] = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
            }
        }

        echo view('AI/ai/settings', [
            'title' => 'Configuration AI',
            'settings' => $settings
        ]);
    }

    private function updateEnv($key, $value)
    {
        $path = Application::getInstance()->getBasePath() . '/.env';
        if (file_exists($path)) {
            $content = file_get_contents($path);

            // Ensure we have a newline at the end before appending if needed
            if (!str_ends_with($content, "\n")) {
                $content .= "\n";
            }

            if (strpos($content, "$key=") !== false) {
                $content = preg_replace("/^$key=.*$/m", "$key=$value", $content);
            } else {
                $content .= "$key=$value\n";
            }

            file_put_contents($path, $content);

            // Force reload env for current request
            if (function_exists('putenv')) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    public function logs()
    {
        $app = Application::getInstance();
        // Placeholder for logs
        $logs = [];

        echo view('AI/ai/logs', [
            'title' => 'Historique AI',
            'logs' => $logs
        ]);
    }

    public function test()
    {
        $app = Application::getInstance();
        $registry = $app->moduleManager->getRegistry();
        $settings = $registry->getSettings('AI');
        $model = $settings['default_model'] ?? 'gpt-3.5-turbo';

        $response = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $prompt = $_POST['prompt'] ?? '';

            if (empty($prompt)) {
                $_SESSION['flash']['warning'] = 'Veuillez entrer un prompt.';
            } else {
                $manager = AIManager::getInstance();
                $client = $manager->getOpenAIClient();

                if ($client) {
                    try {
                        $result = $client->chat()->create([
                            'model' => $model,
                            'messages' => [
                                ['role' => 'user', 'content' => $prompt],
                            ],
                        ]);
                        $response = $result->choices[0]->message->content;
                        $_SESSION['flash']['success'] = 'Réponse reçue avec le modèle: ' . $model;

                        // Log the interaction
                        // TODO: Implement logging to database

                    } catch (\Exception $e) {
                        $_SESSION['flash']['danger'] = 'Erreur API: ' . $e->getMessage();
                        $response = null;
                    }
                } else {
                    $apiKey = getenv('OPENAI_API_KEY');
                    $maskedKey = $apiKey ? substr($apiKey, 0, 7) . '...' : 'Non définie';
                    $_SESSION['flash']['danger'] = 'Client OpenAI non initialisé. Clé API: ' . $maskedKey . '. Configurez la clé dans Settings.';
                }
            }
        }

        echo view('AI/ai/test', [
            'title' => 'Test AI',
            'response' => $response,
            'model' => $model
        ]);
    }
}
