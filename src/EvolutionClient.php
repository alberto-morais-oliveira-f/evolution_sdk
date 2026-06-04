<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk;

use Am2tec\EvolutionSdk\Exceptions\EvolutionException;
use Am2tec\EvolutionSdk\Resources\ChatResource;
use Am2tec\EvolutionSdk\Resources\GroupResource;
use Am2tec\EvolutionSdk\Resources\InstanceResource;
use Am2tec\EvolutionSdk\Resources\MessageResource;
use Am2tec\EvolutionSdk\Resources\SettingsResource;
use Am2tec\EvolutionSdk\Resources\WebhookResource;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;

class EvolutionClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly int $timeout,
    ) {}

    public function instance(): InstanceResource
    {
        return new InstanceResource($this);
    }

    public function message(): MessageResource
    {
        return new MessageResource($this);
    }

    public function chat(): ChatResource
    {
        return new ChatResource($this);
    }

    public function group(): GroupResource
    {
        return new GroupResource($this);
    }

    public function webhook(): WebhookResource
    {
        return new WebhookResource($this);
    }

    public function settings(): SettingsResource
    {
        return new SettingsResource($this);
    }

    public function get(string $endpoint, array $query = []): array
    {
        $response = $this->http
            ->withHeaders(['apikey' => $this->apiKey])
            ->timeout($this->timeout)
            ->get($this->url($endpoint), $query);

        return $this->parse($response);
    }

    public function post(string $endpoint, array $body = []): array
    {
        $response = $this->http
            ->withHeaders(['apikey' => $this->apiKey])
            ->timeout($this->timeout)
            ->post($this->url($endpoint), $body);

        return $this->parse($response);
    }

    public function put(string $endpoint, array $body = []): array
    {
        $response = $this->http
            ->withHeaders(['apikey' => $this->apiKey])
            ->timeout($this->timeout)
            ->put($this->url($endpoint), $body);

        return $this->parse($response);
    }

    public function delete(string $endpoint): array
    {
        $response = $this->http
            ->withHeaders(['apikey' => $this->apiKey])
            ->timeout($this->timeout)
            ->delete($this->url($endpoint));

        return $this->parse($response);
    }

    /** Return a new client instance using a different API key — useful for per-connection keys. */
    public function withKey(string $apiKey): static
    {
        return new static($this->http, $this->baseUrl, $apiKey, $this->timeout);
    }

    /** Return a new client instance with a different request timeout in seconds. */
    public function withTimeout(int $seconds): static
    {
        return new static($this->http, $this->baseUrl, $this->apiKey, $seconds);
    }

    /** No-retry POST for media/slow operations — avoids blocking workers on CDN timeouts. */
    public function postFast(string $endpoint, array $body = [], int $timeout = 10): array
    {
        $response = $this->http
            ->withHeaders(['apikey' => $this->apiKey])
            ->timeout($timeout)
            ->post($this->url($endpoint), $body);

        return $this->parse($response);
    }

    private function url(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');
    }

    private function parse(Response $response): array
    {
        $body = $response->json() ?? [];

        if ($response->failed()) {
            $message = $body['message'] ?? $body['error'] ?? 'Evolution API error';
            if (is_array($message)) {
                $message = implode(', ', $message);
            }

            throw new EvolutionException($message, $response->status(), $body);
        }

        return $body;
    }
}
