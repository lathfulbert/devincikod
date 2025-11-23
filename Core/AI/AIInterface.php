<?php

namespace App\Core\AI;

interface AIInterface
{
    /**
     * Send a prompt to the AI and get a response.
     *
     * @param string $input
     * @return string
     */
    public function respond(string $input): string;

    /**
     * Set the specific model to use (e.g., 'gpt-4', 'gpt-3.5-turbo').
     *
     * @param string $model
     * @return void
     */
    public function setModel(string $model);

    /**
     * Configure the agent with specific parameters.
     *
     * @param array $params
     * @return void
     */
    public function configure(array $params);
}
