<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Tests;

use Am2tec\EvolutionSdk\EvolutionClient;
use Illuminate\Support\Facades\Http;

class InstanceResourceTest extends TestCase
{
    private EvolutionClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = app(EvolutionClient::class);
    }

    public function test_create_posts_to_correct_endpoint(): void
    {
        Http::fake(['*/instance/create' => Http::response(['instance' => ['instanceName' => 'new']], 201)]);

        $result = $this->client->instance()->create('new', ['qrcode' => true]);

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/instance/create')
            && $r['instanceName'] === 'new'
            && $r['qrcode'] === true
        );

        $this->assertArrayHasKey('instance', $result);
    }

    public function test_fetch_all_instances(): void
    {
        Http::fake(['*/instance/fetchInstances' => Http::response([['name' => 'main']], 200)]);

        $result = $this->client->instance()->fetch();

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/instance/fetchInstances'));
        $this->assertCount(1, $result);
    }

    public function test_fetch_single_instance_sends_query_param(): void
    {
        Http::fake(['*/instance/fetchInstances*' => Http::response([['name' => 'main']], 200)]);

        $this->client->instance()->fetch('main');

        Http::assertSent(fn ($r) => str_contains($r->url(), 'instanceName=main'));
    }

    public function test_connect_gets_qr_code(): void
    {
        Http::fake(['*/instance/connect/main' => Http::response(['code' => 'qr-data'], 200)]);

        $result = $this->client->instance()->connect('main');

        $this->assertArrayHasKey('code', $result);
    }

    public function test_connection_state(): void
    {
        Http::fake(['*/instance/connectionState/main' => Http::response(['instance' => ['state' => 'open']], 200)]);

        $result = $this->client->instance()->connectionState('main');

        $this->assertSame('open', $result['instance']['state']);
    }

    public function test_logout_sends_delete(): void
    {
        Http::fake(['*/instance/logout/main' => Http::response([], 200)]);

        $this->client->instance()->logout('main');

        Http::assertSent(fn ($r) => $r->method() === 'DELETE'
            && str_ends_with($r->url(), '/instance/logout/main')
        );
    }

    public function test_delete_instance(): void
    {
        Http::fake(['*/instance/delete/main' => Http::response([], 200)]);

        $this->client->instance()->delete('main');

        Http::assertSent(fn ($r) => $r->method() === 'DELETE');
    }

    public function test_restart_sends_put(): void
    {
        Http::fake(['*/instance/restart/main' => Http::response([], 200)]);

        $this->client->instance()->restart('main');

        Http::assertSent(fn ($r) => $r->method() === 'PUT'
            && str_ends_with($r->url(), '/instance/restart/main')
        );
    }
}
