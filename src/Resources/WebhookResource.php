<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Resources;

use Am2tec\EvolutionSdk\EvolutionClient;

class WebhookResource
{
    public function __construct(private readonly EvolutionClient $client) {}

    /**
     * @param  array<string, string>  $headers  Custom headers forwarded with each webhook delivery.
     */
    public function set(string $instance, string $url, array $events = [], bool $webhookBase64 = false, bool $byEvents = false, bool $enabled = true, array $headers = []): array
    {
        $payload = [
            'url'             => $url,
            'enabled'         => $enabled,
            'webhookBase64'   => $webhookBase64,
            'webhookByEvents' => $byEvents,
            'events'          => $events ?: $this->defaultEvents(),
        ];

        if (! empty($headers)) {
            $payload['headers'] = $headers;
        }

        return $this->client->post("webhook/set/{$instance}", $payload);
    }

    public function get(string $instance): array
    {
        return $this->client->get("webhook/find/{$instance}");
    }

    private function defaultEvents(): array
    {
        return [
            'APPLICATION_STARTUP',
            'QRCODE_UPDATED',
            'MESSAGES_SET',
            'MESSAGES_UPSERT',
            'MESSAGES_UPDATE',
            'MESSAGES_DELETE',
            'SEND_MESSAGE',
            'CONTACTS_SET',
            'CONTACTS_UPSERT',
            'CONTACTS_UPDATE',
            'PRESENCE_UPDATE',
            'CHATS_SET',
            'CHATS_UPSERT',
            'CHATS_UPDATE',
            'CHATS_DELETE',
            'GROUPS_UPSERT',
            'GROUPS_UPDATE',
            'GROUP_PARTICIPANTS_UPDATE',
            'CONNECTION_UPDATE',
            'CALL',
            'LABELS_EDIT',
            'LABELS_ASSOCIATION',
        ];
    }
}
