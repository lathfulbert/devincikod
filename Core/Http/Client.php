<?php

namespace App\Core\Http;

use App\Core\Http\Exceptions\RequestException;
use App\Core\Http\Exceptions\ConnectionException;

/**
 * HTTP Client
 * 
 * cURL-based HTTP client for making external API requests.
 */
class Client
{
    protected int $timeout = 30;
    protected bool $verifySSL = true;
    protected array $defaultHeaders = [];

    public function __construct(array $config = [])
    {
        $this->timeout = $config['timeout'] ?? 30;
        $this->verifySSL = $config['verify'] ?? true;
        $this->defaultHeaders = $config['headers'] ?? [];
    }

    /**
     * Send an HTTP request
     */
    public function send(Request $request): Response
    {
        $ch = curl_init();

        // Build URL with query parameters if GET
        $url = $request->url();
        if ($request->method() === 'GET' && $request->body()) {
            $url .= '?' . http_build_query($request->body());
        }

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $request->option('timeout', $this->timeout),
            CURLOPT_SSL_VERIFYPEER => $request->option('verify', $this->verifySSL),
            CURLOPT_SSL_VERIFYHOST => $request->option('verify', $this->verifySSL) ? 2 : 0,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => $request->method(),
        ]);

        // Set headers
        $headers = array_merge($this->defaultHeaders, $request->headers());
        if (!empty($headers)) {
            $curlHeaders = [];
            foreach ($headers as $key => $value) {
                $curlHeaders[] = "{$key}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        // Set body for non-GET requests
        if ($request->method() !== 'GET' && $request->body() !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body());
        }

        // Execute request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            throw new ConnectionException("cURL error ({$errno}): {$error}");
        }

        // Get info
        $info = curl_getinfo($ch);
        $headerSize = $info['header_size'];
        $statusCode = $info['http_code'];

        // Split headers and body
        $headerString = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        // Parse headers
        $headers = $this->parseHeaders($headerString);

        return new Response($body, $statusCode, $headers, $info);
    }

    /**
     * Make a GET request
     */
    public function get(string $url, array $query = []): Response
    {
        $request = new Request('GET', $url);
        if (!empty($query)) {
            $request->withBody($query);
        }
        return $this->send($request);
    }

    /**
     * Make a POST request
     */
    public function post(string $url, array $data = []): Response
    {
        $request = (new Request('POST', $url))
            ->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json']);

        return $this->send($request);
    }

    /**
     * Make a PUT request
     */
    public function put(string $url, array $data = []): Response
    {
        $request = (new Request('PUT', $url))
            ->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json']);

        return $this->send($request);
    }

    /**
     * Make a PATCH request
     */
    public function patch(string $url, array $data = []): Response
    {
        $request = (new Request('PATCH', $url))
            ->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json']);

        return $this->send($request);
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $url): Response
    {
        $request = new Request('DELETE', $url);
        return $this->send($request);
    }

    /**
     * Make a HEAD request
     */
    public function head(string $url): Response
    {
        $request = new Request('HEAD', $url);
        return $this->send($request);
    }

    /**
     * Parse headers from header string
     */
    protected function parseHeaders(string $headerString): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerString);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        return $headers;
    }
}
