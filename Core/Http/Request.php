<?php

namespace App\Core\Http;

/**
 * HTTP Request
 * 
 * Represents an HTTP request with method, URL, headers, body, and options.
 */
class Request
{
    protected string $method;
    protected string $url;
    protected array $headers = [];
    protected mixed $body = null;
    protected array $options = [];

    public function __construct(string $method, string $url)
    {
        $this->method = strtoupper($method);
        $this->url = $url;
    }

    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get URL
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Set headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Get headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Set body
     */
    public function withBody(mixed $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get body
     */
    public function body(): mixed
    {
        return $this->body;
    }

    /**
     * Set option
     */
    public function withOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Set options
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Get option
     */
    public function option(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Get all options
     */
    public function options(): array
    {
        return $this->options;
    }
}
