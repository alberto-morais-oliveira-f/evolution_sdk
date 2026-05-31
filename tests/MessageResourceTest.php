<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Tests;

use Am2tec\EvolutionSdk\EvolutionClient;
use Illuminate\Support\Facades\Http;

class MessageResourceTest extends TestCase
{
    private EvolutionClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = app(EvolutionClient::class);
    }

    public function test_send_text_posts_number_and_text(): void
    {
        Http::fake(['*/message/sendText/*' => Http::response(['key' => ['id' => 'msg-id']], 201)]);

        $result = $this->client->message()->sendText('main', '5511999999999', 'Olá!');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/message/sendText/main')
            && $r['number'] === '5511999999999'
            && $r['text'] === 'Olá!'
        );

        $this->assertArrayHasKey('key', $result);
    }

    public function test_send_text_passes_extra_options(): void
    {
        Http::fake(['*/message/sendText/*' => Http::response([], 201)]);

        $this->client->message()->sendText('main', '5511999999999', 'Hi', ['delay' => 1000]);

        Http::assertSent(fn ($r) => $r['delay'] === 1000);
    }

    public function test_send_media_posts_number_and_media(): void
    {
        Http::fake(['*/message/sendMedia/*' => Http::response([], 201)]);

        $this->client->message()->sendMedia('main', '5511999999999', [
            'mediatype' => 'image',
            'media'     => 'https://example.com/img.jpg',
            'caption'   => 'Foto',
        ]);

        Http::assertSent(fn ($r) => $r['number'] === '5511999999999'
            && $r['mediatype'] === 'image'
        );
    }

    public function test_send_location_posts_coordinates(): void
    {
        Http::fake(['*/message/sendLocation/*' => Http::response([], 201)]);

        $this->client->message()->sendLocation('main', '5511999999999', -23.5505, -46.6333, 'São Paulo');

        Http::assertSent(fn ($r) => $r['latitude'] === -23.5505
            && $r['longitude'] === -46.6333
            && $r['name'] === 'São Paulo'
        );
    }

    public function test_send_poll_posts_required_fields(): void
    {
        Http::fake(['*/message/sendPoll/*' => Http::response([], 201)]);

        $this->client->message()->sendPoll('main', '5511999999999', 'Qual opção?', 1, ['A', 'B', 'C']);

        Http::assertSent(fn ($r) => $r['name'] === 'Qual opção?'
            && $r['selectableCount'] === 1
            && $r['values'] === ['A', 'B', 'C']
        );
    }

    public function test_send_reaction_posts_key_and_reaction(): void
    {
        Http::fake(['*/message/sendReaction/*' => Http::response([], 201)]);

        $key = ['id' => 'msg-123', 'remoteJid' => '5511999999999@s.whatsapp.net', 'fromMe' => false];

        $this->client->message()->sendReaction('main', '5511999999999', $key, '👍');

        Http::assertSent(fn ($r) => $r['reaction'] === '👍'
            && $r['key']['id'] === 'msg-123'
        );
    }

    public function test_send_buttons_includes_number(): void
    {
        Http::fake(['*/message/sendButtons/*' => Http::response([], 201)]);

        $this->client->message()->sendButtons('main', '5511999999999', [
            'title'   => 'Escolha',
            'buttons' => [['buttonId' => '1', 'buttonText' => ['displayText' => 'Opção 1']]],
        ]);

        Http::assertSent(fn ($r) => $r['number'] === '5511999999999'
            && $r['title'] === 'Escolha'
        );
    }
}
