<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk;

use Am2tec\EvolutionSdk\Exceptions\EvolutionException;
use Am2tec\EvolutionSdk\Resources\ChatResource;
use Am2tec\EvolutionSdk\Resources\GroupResource;
use Am2tec\EvolutionSdk\Resources\InstanceResource;
use Am2tec\EvolutionSdk\Resources\MessageResource;
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
