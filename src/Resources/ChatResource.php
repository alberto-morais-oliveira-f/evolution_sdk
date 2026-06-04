<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Resources;

use Am2tec\EvolutionSdk\EvolutionClient;

class ChatResource
{
    public function __construct(private readonly EvolutionClient $client) {}

    public function checkIsWhatsApp(string $instance, array $numbers): array
    {
        return $this->client->post("chat/whatsappNumbers/{$instance}", ['numbers' => $numbers]);
    }

    public function findChats(string $instance): array
    {
        return $this->client->post("chat/findChats/{$instance}");
    }

    public function findContacts(string $instance, ?string $where = null): array
    {
        $body = $where ? ['where' => ['id' => $where]] : [];

        return $this->client->post("chat/findContacts/{$instance}", $body);
    }

    public function findMessages(string $instance, array $where = [], ?int $limit = null): array
    {
        $body = ['where' => $where];

        if ($limit !== null) {
            $body['limit'] = $limit;
        }

        return $this->client->post("chat/findMessages/{$instance}", $body);
    }

    public function markAsRead(string $instance, array $readMessages): array
    {
        return $this->client->post("chat/markMessageAsRead/{$instance}", [
            'readMessages' => $readMessages,
        ]);
    }

    public function deleteMessage(string $instance, array $message): array
    {
        return $this->client->delete("chat/deleteMessageForEveryone/{$instance}");
    }

    public function fetchProfilePicture(string $instance, string $number): array
    {
        return $this->client->get("chat/fetchProfilePictureUrl/{$instance}", ['number' => $number]);
    }

    public function sendPresence(string $instance, string $number, string $presence = 'composing'): array
    {
        return $this->client->post("chat/sendPresence/{$instance}", [
            'number'   => $number,
            'presence' => $presence,
        ]);
    }

    public function updateBlockStatus(string $instance, string $number, string $status): array
    {
        return $this->client->post("chat/updateBlockStatus/{$instance}", [
            'number' => $number,
            'status' => $status,
        ]);
    }

    /** @param array<string, mixed> $message */
    public function getMediaBase64(string $instance, array $message, bool $convertToMp4 = false): array
    {
        return $this->client->post("chat/getBase64FromMediaMessage/{$instance}", [
            'message'      => $message,
            'convertToMp4' => $convertToMp4,
        ]);
    }

    /**
     * Fetch a single message by its Evolution message ID.
     *
     * @return array<string, mixed>
     */
    public function findById(string $instance, string $messageId, int $timeout = 8): array
    {
        $response = $this->client->postFast("chat/findMessages/{$instance}", [
            'where' => ['key' => ['id' => $messageId]],
            'limit' => 1,
        ], $timeout);

        if (is_array($response) && array_is_list($response)) {
            return $response[0] ?? [];
        }

        return $response['messages']['records'][0] ?? [];
    }

    /**
     * Fetch all messages for an instance (API ignores JID filter).
     * Returns records sorted by timestamp desc.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(string $instance, int $limit = 10000, int $timeout = 120): array
    {
        $response = $this->client->postFast("chat/findMessages/{$instance}", [
            'where' => (object) [],
            'limit' => $limit,
        ], $timeout);

        if (isset($response['messages']['records']) && is_array($response['messages']['records'])) {
            return $response['messages']['records'];
        }

        if (is_array($response) && array_is_list($response)) {
            return $response;
        }

        return [];
    }
}
