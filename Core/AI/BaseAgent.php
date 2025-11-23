<?php

namespace App\Core\AI;

abstract class BaseAgent implements AIInterface
{
    protected $model = 'gpt-3.5-turbo';
    protected $config = [];
    protected $client;

    public function __construct()
    {
        $this->client = AIManager::getInstance()->getOpenAIClient();
    }

    public function setModel(string $model)
    {
        $this->model = $model;
    }

    public function configure(array $params)
    {
        $this->config = array_merge($this->config, $params);
    }

    public function respond(string $input): string
    {
        if (!$this->client) {
            return "Error: OpenAI client not initialized. Check API Key.";
        }

        $params = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $input],
            ],
        ];

        if (isset($this->config['temperature'])) {
            $params['temperature'] = $this->config['temperature'];
        }

        try {
            $response = $this->client->chat()->create($params);
            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
