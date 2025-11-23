<?php

namespace App\Core\AI;

use OpenAI;

class AIManager
{
    private static $instance;
    private $agents = [];
    private $openAIClient;
    private $defaultModel = 'gpt-3.5-turbo';

    private function __construct()
    {
        $apiKey = getenv('OPENAI_API_KEY');
        if ($apiKey && class_exists('OpenAI')) {
            $this->openAIClient = OpenAI::client($apiKey);
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new AIManager();
        }
        return self::$instance;
    }

    public function registerAgent(string $module, AIInterface $agent)
    {
        $this->agents[$module] = $agent;
    }

    public function getAgent(string $module): ?AIInterface
    {
        return $this->agents[$module] ?? null;
    }

    public function getOpenAIClient()
    {
        return $this->openAIClient;
    }

    public function getAllAgents(): array
    {
        return $this->agents;
    }
}
