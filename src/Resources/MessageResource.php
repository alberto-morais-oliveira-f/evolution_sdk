<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Resources;

use Am2tec\EvolutionSdk\EvolutionClient;

class MessageResource
{
    public function __construct(private readonly EvolutionClient $client) {}

    public function sendText(string $instance, string $to, string $text, array $options = []): array
    {
        return $this->client->post("message/sendText/{$instance}", array_merge([
            'number' => $to,
            'text'   => $text,
        ], $options));
    }

    public function sendMedia(string $instance, string $to, array $media): array
    {
        return $this->client->post("message/sendMedia/{$instance}", array_merge([
            'number' => $to,
        ], $media));
    }

    public function sendAudio(string $instance, string $to, string $audioUrl): array
    {
        return $this->client->post("message/sendWhatsAppAudio/{$instance}", [
            'number' => $to,
            'audio'  => $audioUrl,
        ]);
    }

    public function sendLocation(string $instance, string $to, float $lat, float $lng, string $name = '', string $address = ''): array
    {
        return $this->client->post("message/sendLocation/{$instance}", [
            'number'    => $to,
            'name'      => $name,
            'address'   => $address,
            'latitude'  => $lat,
            'longitude' => $lng,
        ]);
    }

    public function sendContact(string $instance, string $to, array $contact): array
    {
        return $this->client->post("message/sendContact/{$instance}", [
            'number'  => $to,
            'contact' => $contact,
        ]);
    }

    public function sendButtons(string $instance, string $to, array $data): array
    {
        return $this->client->post("message/sendButtons/{$instance}", array_merge([
            'number' => $to,
        ], $data));
    }

    public function sendList(string $instance, string $to, array $data): array
    {
        return $this->client->post("message/sendList/{$instance}", array_merge([
            'number' => $to,
        ], $data));
    }

    public function sendPoll(string $instance, string $to, string $name, int $selectableCount, array $values): array
    {
        return $this->client->post("message/sendPoll/{$instance}", [
            'number'          => $to,
            'name'            => $name,
            'selectableCount' => $selectableCount,
            'values'          => $values,
        ]);
    }

    public function sendReaction(string $instance, string $to, array $key, string $reaction): array
    {
        return $this->client->post("message/sendReaction/{$instance}", [
            'key'      => $key,
            'reaction' => $reaction,
        ]);
    }

    public function sendSticker(string $instance, string $to, string $stickerUrl): array
    {
        return $this->client->post("message/sendSticker/{$instance}", [
            'number'  => $to,
            'sticker' => $stickerUrl,
        ]);
    }

    public function sendStatus(string $instance, array $data): array
    {
        return $this->client->post("message/sendStatus/{$instance}", $data);
    }
}
