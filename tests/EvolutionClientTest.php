<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Tests;

use Am2tec\EvolutionSdk\EvolutionClient;
use Am2tec\EvolutionSdk\Exceptions\EvolutionException;
use Am2tec\EvolutionSdk\Facades\Evolution;
use Am2tec\EvolutionSdk\Resources\ChatResource;
use Am2tec\EvolutionSdk\Resources\GroupResource;
use Am2tec\EvolutionSdk\Resources\InstanceResource;
use Am2tec\EvolutionSdk\Resources\MessageResource;
use Am2tec\EvolutionSdk\Resources\WebhookResource;
use Illuminate\Support\Facades\Http;

class EvolutionClientTest extends TestCase
{
    public function test_facade_resolves_client(): void
    {
        $this->assertInstanceOf(EvolutionClient::class, Evolution::getFacadeRoot());
    }

    public function test_resources_return_correct_types(): void
    {
        $client = app(EvolutionClient::class);

        $this->assertInstanceOf(InstanceResource::class, $client->instance());
        $this->assertInstanceOf(MessageResource::class, $client->message());
        $this->assertInstanceOf(ChatResource::class, $client->chat());
        $this->assertInstanceOf(GroupResource::class, $client->group());
        $this->assertInstanceOf(WebhookResource::class, $client->webhook());
    }

    public function test_get_request_sends_api_key_header(): void
    {
        Http::fake(['*' => Http::response(['status' => 200], 200)]);

        app(EvolutionClient::class)->get('instance/fetchInstances');

        Http::assertSent(fn ($request) => $request->hasHeader('apikey', 'test-api-key'));
    }

    public function test_post_request_sends_json_body(): void
    {
        Http::fake(['*' => Http::response(['key' => 'instance'], 201)]);

        $result = app(EvolutionClient::class)->post('instance/create', ['instanceName' => 'my-instance']);

        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8080/instance/create'
            && $request['instanceName'] === 'my-instance'
        );

        $this->assertSame('instance', $result['key']);
    }

    public function test_throws_evolution_exception_on_4xx(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $this->expectException(EvolutionException::class);
        $this->expectExceptionMessage('Unauthorized');

        app(EvolutionClient::class)->get('instance/fetchInstances');
    }

    public function test_throws_evolution_exception_on_5xx(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Internal Server Error'], 500)]);

        $this->expectException(EvolutionException::class);

        app(EvolutionClient::class)->get('instance/fetchInstances');
    }

    public function test_evolution_exception_carries_status_code(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Not Found'], 404)]);

        try {
            app(EvolutionClient::class)->get('instance/fetchInstances');
            $this->fail('Expected EvolutionException');
        } catch (EvolutionException $e) {
            $this->assertSame(404, $e->getStatusCode());
            $this->assertSame(['message' => 'Not Found'], $e->getResponseBody());
        }
    }

    public function test_delete_request_hits_correct_url(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        app(EvolutionClient::class)->delete('instance/delete/main');

        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8080/instance/delete/main'
            && $request->method() === 'DELETE'
        );
    }

    public function test_put_request_hits_correct_url(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        app(EvolutionClient::class)->put('instance/restart/main');

        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8080/instance/restart/main'
            && $request->method() === 'PUT'
        );
    }
}
