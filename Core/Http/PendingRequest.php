<?php

namespace App\Core\Http;

use App\Core\Http\Exceptions\RequestException;

/**
 * Pending HTTP Request
 * 
 * Fluent API for building and sending HTTP requests.
 */
class PendingRequest
{
    protected Client $client;
    protected array $headers = [];
    protected array $options = [];
    protected ?string $bodyFormat = null;
    protected array $attachments = [];
    protected int $retryTimes = 0;
    protected int $retrySleep = 0;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    /**
     * Set custom headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set Bearer token
     */
    public function withToken(string $token, string $type = 'Bearer'): self
    {
        return $this->withHeaders(['Authorization' => "{$type} {$token}"]);
    }

    /**
     * Set Basic authentication
     */
    public function withBasicAuth(string $username, string $password): self
    {
        $credentials = base64_encode("{$username}:{$password}");
        return $this->withHeaders(['Authorization' => "Basic {$credentials}"]);
    }

    /**
     * Set Accept header
     */
    public function accept(string $contentType): self
    {
        return $this->withHeaders(['Accept' => $contentType]);
    }

    /**
     * Set Content-Type header
     */
    public function contentType(string $contentType): self
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * Set request timeout
     */
    public function timeout(int $seconds): self
    {
        $this->options['timeout'] = $seconds;
        return $this;
    }

    /**
     * Enable/disable SSL verification
     */
    public function withoutVerifying(): self
    {
        $this->options['verify'] = false;
        return $this;
    }

    /**
     * Set retry configuration
     */
    public function retry(int $times, int $sleepMilliseconds = 0): self
    {
        $this->retryTimes = $times;
        $this->retrySleep = $sleepMilliseconds;
        return $this;
    }

    /**
     * Send as JSON
     */
    public function asJson(): self
    {
        $this->bodyFormat = 'json';
        return $this->contentType('application/json');
    }

    /**
     * Send as form data
     */
    public function asForm(): self
    {
        $this->bodyFormat = 'form';
        return $this->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Send as multipart (for file uploads)
     */
    public function asMultipart(): self
    {
        $this->bodyFormat = 'multipart';
        return $this;
    }

    /**
     * Attach a file
     */
    public function attach(string $name, $content, string $filename = null): self
    {
        $this->attachments[$name] = [
            'content' => $content,
            'filename' => $filename ?? $name,
        ];

        $this->asMultipart();
        return $this;
    }

    /**
     * Make GET request
     */
    public function get(string $url, array $query = []): Response
    {
        return $this->send('GET', $url, $query);
    }

    /**
     * Make POST request
     */
    public function post(string $url, array $data = []): Response
    {
        return $this->send('POST', $url, $data);
    }

    /**
     * Make PUT request
     */
    public function put(string $url, array $data = []): Response
    {
        return $this->send('PUT', $url, $data);
    }

    /**
     * Make PATCH request
     */
    public function patch(string $url, array $data = []): Response
    {
        return $this->send('PATCH', $url, $data);
    }

    /**
     * Make DELETE request
     */
    public function delete(string $url): Response
    {
        return $this->send('DELETE', $url);
    }

    /**
     * Make HEAD request
     */
    public function head(string $url): Response
    {
        return $this->send('HEAD', $url);
    }

    /**
     * Send the request
     */
    protected function send(string $method, string $url, $data = null): Response
    {
        $request = new Request($method, $url);
        $request->withHeaders($this->headers);
        $request->withOptions($this->options);

        // Format body based on bodyFormat
        if ($data !== null) {
            $body = $this->prepareBody($data);
            $request->withBody($body);
        }

        // Handle attachments
        if (!empty($this->attachments) && $data !== null) {
            $body = $this->prepareMultipartBody($data);
            $request->withBody($body);
        }

        // Send with retry
        return $this->sendWithRetry($request);
    }

    /**
     * Prepare request body based on format
     */
    protected function prepareBody($data)
    {
        if ($this->bodyFormat === 'json') {
            return json_encode($data);
        } elseif ($this->bodyFormat === 'form') {
            return http_build_query($data);
        } elseif ($this->bodyFormat === 'multipart') {
            return $this->prepareMultipartBody($data);
        }

        return $data;
    }

    /**
     * Prepare multipart body for file uploads
     */
    protected function prepareMultipartBody(array $data): string
    {
        $boundary = '----WebKitFormBoundary' . uniqid();
        $this->withHeaders(['Content-Type' => "multipart/form-data; boundary={$boundary}"]);

        $body = '';

        // Add regular fields
        foreach ($data as $key => $value) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"{$key}\"\r\n\r\n";
            $body .= "{$value}\r\n";
        }

        // Add file attachments
        foreach ($this->attachments as $name => $file) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$file['filename']}\"\r\n";
            $body .= "Content-Type: application/octet-stream\r\n\r\n";
            $body .= $file['content'] . "\r\n";
        }

        $body .= "--{$boundary}--\r\n";

        return $body;
    }

    /**
     * Send request with retry logic
     */
    protected function sendWithRetry(Request $request): Response
    {
        $attempt = 0;

        while (true) {
            try {
                return $this->client->send($request);
            } catch (RequestException $e) {
                $attempt++;

                if ($attempt >= $this->retryTimes + 1) {
                    throw $e;
                }

                if ($this->retrySleep > 0) {
                    usleep($this->retrySleep * 1000);
                }
            }
        }
    }
}
