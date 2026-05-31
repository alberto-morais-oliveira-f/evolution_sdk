<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Tests;

use Am2tec\EvolutionSdk\EvolutionClient;
use Illuminate\Support\Facades\Http;

class WebhookResourceTest extends TestCase
{
    private EvolutionClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = app(EvolutionClient::class);
    }

    public function test_set_webhook_posts_url_and_events(): void
    {
        Http::fake(['*/webhook/set/*' => Http::response(['webhook' => ['url' => 'https://myapp.com/hook']], 200)]);

        $this->client->webhook()->set('main', 'https://myapp.com/hook');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/webhook/set/main')
            && $r['url'] === 'https://myapp.com/hook'
            && is_array($r['events'])
            && count($r['events']) > 0
        );
    }

    public function test_set_webhook_with_custom_events(): void
    {
        Http::fake(['*/webhook/set/*' => Http::response([], 200)]);

        $this->client->webhook()->set('main', 'https://myapp.com/hook', ['MESSAGES_UPSERT', 'CONNECTION_UPDATE']);

        Http::assertSent(fn ($r) => $r['events'] === ['MESSAGES_UPSERT', 'CONNECTION_UPDATE']);
    }

    public function test_get_webhook_hits_find_endpoint(): void
    {
        Http::fake(['*/webhook/find/main' => Http::response(['webhook' => ['url' => 'https://myapp.com/hook']], 200)]);

        $result = $this->client->webhook()->get('main');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/webhook/find/main')
            && $r->method() === 'GET'
        );

        $this->assertSame('https://myapp.com/hook', $result['webhook']['url']);
    }
}
