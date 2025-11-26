<?php

namespace App\Core\Http\Exceptions;

use Exception;
use App\Core\Http\Response;

/**
 * Request Exception
 * 
 * Thrown when an HTTP request fails.
 */
class RequestException extends Exception
{
    protected ?Response $response;

    public function __construct(string $message, ?Response $response = null, int $code = 0)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }

    /**
     * Get the response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Check if there is a response
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }
}
