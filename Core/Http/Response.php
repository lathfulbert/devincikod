<?php

namespace App\Core\Http;

/**
 * HTTP Response
 * 
 * Wrapper for HTTP response providing easy access to body, JSON, headers, status.
 */
class Response
{
    protected string $body;
    protected int $statusCode;
    protected array $headers;
    protected array $info;

    public function __construct(string $body, int $statusCode, array $headers, array $info = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->info = $info;
    }

    /**
     * Get response body as string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Parse response body as JSON
     */
    public function json(): array
    {
        return json_decode($this->body, true) ?? [];
    }

    /**
     * Get HTTP status code
     */
    public function status(): int
    {
        return $this->statusCode;
    }

    /**
     * Check if response was successful (2xx)
     */
    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if response failed
     */
    public function failed(): bool
    {
        return !$this->successful();
    }

    /**
     * Check if response is OK (200)
     */
    public function ok(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * Check if response is created (201)
     */
    public function created(): bool
    {
        return $this->statusCode === 201;
    }

    /**
     * Check if response is no content (204)
     */
    public function noContent(): bool
    {
        return $this->statusCode === 204;
    }

    /**
     * Check if response is redirect (3xx)
     */
    public function redirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Check if response is client error (4xx)
     */
    public function clientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if response is server error (5xx)
     */
    public function serverError(): bool
    {
        return $this->statusCode >= 500;
    }

    /**
     * Get a specific header
     */
    public function header(string $key): ?string
    {
        return $this->headers[strtolower($key)] ?? null;
    }

    /**
     * Get all headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get request info from cURL
     */
    public function info(): array
    {
        return $this->info;
    }

    /**
     * Get response as array (for debugging)
     */
    public function toArray(): array
    {
        return [
            'status' => $this->statusCode,
            'headers' => $this->headers,
            'body' => $this->body,
        ];
    }

    /**
     * Convert response to string
     */
    public function __toString(): string
    {
        return $this->body;
    }
}
