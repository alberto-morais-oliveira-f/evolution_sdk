<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Tests;

use Am2tec\EvolutionSdk\Webhook\EvolutionPayloadMapper;
use Carbon\Carbon;

class WebhookPayloadMapperTest extends TestCase
{
    private EvolutionPayloadMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new EvolutionPayloadMapper();
    }

    public function test_maps_plain_text_message(): void
    {
        $payload = $this->makePayload('5511999990001@s.whatsapp.net', [
            'conversation' => 'Hello',
        ], pushName: 'João');

        $result = $this->mapper->mapMessage($payload);

        $this->assertSame('msg-001', $result['evolution_message_id']);
        $this->assertSame('5511999990001', $result['phone']);
        $this->assertSame('João', $result['name']);
        $this->assertSame('text', $result['type']);
        $this->assertSame('Hello', $result['body']);
        $this->assertNull($result['media_url']);
        $this->assertFalse($result['from_me']);
        $this->assertFalse($result['is_group']);
        $this->assertNull($result['sender_name']);
        $this->assertInstanceOf(Carbon::class, $result['timestamp']);
    }

    public function test_maps_extended_text_message(): void
    {
        $payload = $this->makePayload('5511999990001@s.whatsapp.net', [
            'extendedTextMessage' => ['text' => 'Extended hello'],
        ]);

        $result = $this->mapper->mapMessage($payload);

        $this->assertSame('text', $result['type']);
        $this->assertSame('Extended hello', $result['body']);
    }

    public function test_maps_image_message(): void
    {
        $payload = $this->makePayload('5511999990001@s.whatsapp.net', [
            'imageMessage' => [
                'caption' => 'Look at this',
                'url' => 'https://cdn.example.com/image.jpg',
            ],
        ]);

        $result = $this->mapper->mapMessage($payload);

        $this->assertSame('image', $result['type']);
        $this->assertSame('Look at this', $result['body']);
        $this->assertSame('https://cdn.example.com/image.jpg', $result['media_url']);
    }

    public function test_maps_audio_message(): void
    {
        $payload = $this->makePayload('5511999990001@s.whatsapp.net', [
            'audioMessage' => ['url' => 'https://cdn.example.com/audio.ogg'],
        ]);

        $result = $this->mapper->mapMessage($payload);

        $this->assertSame('audio', $result['type']);
        $this->assertNull($result['body']);
        $this->assertSame('https://cdn.example.com/audio.ogg', $result['media_url']);
    }

    public function test_maps_document_message(): void
    {
        $payload = $this->makePayload('5511999990001@s.whatsapp.net', [
            'documentMessage' => [
                'fileName' => 'contract.pdf',
                'url' => 'https://cdn.example.com/contract.pdf',
            ],
        ]);

        $result = $this->mapper->mapMessage($payload);

        $this->assertSame('document', $result['type']);
        $this->assertSame('contract.pdf', $result['body']);
    }

    public function test_maps_group_message_with_sender_name(): void
    {
        $payload = $this->makePayload('5511888880000@g.us', [
            'conversation' => 'Group message',
        ], pushName: 'Maria', participant: '5511777770002@s.whatsapp.net');

        $result = $this->mapper->mapMessage($payload);

        $this->assertTrue($result['is_group']);
        $this->assertSame('5511888880000', $result['phone']);
        $this->assertNull($result['name']); // groups have no contact name
        $this->assertStringContainsString('Maria', $result['sender_name']);
        $this->assertStringContainsString('5511777770002', $result['sender_name']);
    }

    public function test_marks_from_me_messages(): void
    {
        $payload = $this->makePayload('5511999990001@s.whatsapp.net', [
            'conversation' => 'Sent by me',
        ], fromMe: true);

        $result = $this->mapper->mapMessage($payload);

        $this->assertTrue($result['from_me']);
    }

    public function test_handles_empty_payload_gracefully(): void
    {
        $result = $this->mapper->mapMessage([]);

        $this->assertSame('', $result['evolution_message_id']);
        $this->assertSame('', $result['phone']);
        $this->assertSame('text', $result['type']);
        $this->assertNull($result['body']);
        $this->assertFalse($result['is_group']);
        $this->assertInstanceOf(Carbon::class, $result['timestamp']);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function makePayload(
        string $remoteJid,
        array $message,
        string $pushName = '',
        string $participant = '',
        bool $fromMe = false,
        string $messageId = 'msg-001',
    ): array {
        return [
            'data' => [
                'key' => [
                    'remoteJid' => $remoteJid,
                    'id' => $messageId,
                    'fromMe' => $fromMe,
                    'participant' => $participant ?: null,
                ],
                'message' => $message,
                'pushName' => $pushName ?: null,
                'messageTimestamp' => 1700000000,
            ],
        ];
    }
}
