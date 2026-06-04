<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Webhook;

use Carbon\Carbon;

class EvolutionPayloadMapper
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *   evolution_message_id: string,
     *   phone: string,
     *   name: string|null,
     *   sender_name: string|null,
     *   is_group: bool,
     *   from_me: bool,
     *   type: string,
     *   body: string|null,
     *   media_url: string|null,
     *   timestamp: Carbon,
     * }
     */
    public function mapMessage(array $payload): array
    {
        $data = $payload['data'] ?? [];
        $key = $data['key'] ?? [];
        $msg = $data['message'] ?? [];

        $remoteJid = $key['remoteJid'] ?? '';
        $isGroup = str_contains($remoteJid, '@g.us');
        $phone = $this->extractPhone($remoteJid);

        [$type, $body, $mediaUrl] = $this->extractContent($msg);

        // For groups: pushName is the sender's display name on live webhooks.
        // In history sync, Evolution returns the group phone as pushName — discard that.
        // key.participant holds the sender's JID; use its phone as fallback.
        $pushName = $data['pushName'] ?? null;
        $participantPhone = $isGroup ? $this->extractPhone($key['participant'] ?? '') : null;
        $validPushName = ($pushName && $pushName !== $phone) ? $pushName : null;
        $senderName = $isGroup
            ? ($validPushName && $participantPhone
                ? "{$validPushName} ({$participantPhone})"
                : ($validPushName ?: $participantPhone ?: null))
            : null;
        $contactName = $isGroup ? null : $pushName;

        return [
            'evolution_message_id' => $key['id'] ?? '',
            'phone' => $phone,
            'name' => $contactName,
            'sender_name' => $senderName,
            'is_group' => $isGroup,
            'from_me' => (bool) ($key['fromMe'] ?? false),
            'type' => $type,
            'body' => $body,
            'media_url' => $mediaUrl,
            'timestamp' => now()->setTimestamp($data['messageTimestamp'] ?? now()->timestamp),
        ];
    }

    /**
     * Normalize a messages.update payload entry into a uniform shape.
     * Evolution sends either a flat object { id, status, ... } or
     * a nested object { key: { id }, update: { status } }.
     *
     * @param  array<string, mixed>  $update
     * @return array{evolution_id: string|null, status: string|null}
     */
    public function mapMessageUpdate(array $update): array
    {
        $evolutionId = $update['id'] ?? $update['key']['id'] ?? null;
        $rawStatus = strtolower((string) ($update['status'] ?? $update['update']['status'] ?? ''));

        $status = match ($rawStatus) {
            'pending' => 'pending',
            'server_ack' => 'server_ack',
            'delivery_ack' => 'delivery_ack',
            'read', 'played' => 'read',
            default => null,
        };

        return [
            'evolution_id' => $evolutionId,
            'status' => $status,
        ];
    }

    /**
     * Normalize a connection.update payload into a uniform shape.
     *
     * @param  array<string, mixed>  $payload
     * @return array{instance: string, status: string}
     */
    public function mapConnectionUpdate(array $payload): array
    {
        $state = $payload['data']['state'] ?? 'disconnected';

        $status = match ($state) {
            'open' => 'connected',
            'close' => 'disconnected',
            default => 'qr_pending',
        };

        return [
            'instance' => $payload['instance'] ?? '',
            'status' => $status,
        ];
    }

    /**
     * Extract the QR code base64 string from a qrcode.updated payload.
     * Handles Evolution v1 (base64 at data root) and v2 (wrapped in qrcode key).
     *
     * @param  array<string, mixed>  $payload
     */
    public function extractQrCode(array $payload): ?string
    {
        $data = $payload['data'] ?? [];

        return $data['qrcode']['base64'] ?? $data['base64'] ?? null;
    }

    private function extractPhone(string $remoteJid): string
    {
        return preg_replace('/@.*/', '', $remoteJid) ?? $remoteJid;
    }

    /** @param array<string, mixed> $msg */
    /** @return array{0: string, 1: string|null, 2: string|null} */
    private function extractContent(array $msg): array
    {
        if (isset($msg['conversation'])) {
            return ['text', $msg['conversation'], null];
        }

        if (isset($msg['extendedTextMessage']['text'])) {
            return ['text', $msg['extendedTextMessage']['text'], null];
        }

        if (isset($msg['imageMessage'])) {
            return ['image', $msg['imageMessage']['caption'] ?? null, $msg['imageMessage']['url'] ?? null];
        }

        if (isset($msg['audioMessage'])) {
            return ['audio', null, $msg['audioMessage']['url'] ?? null];
        }

        if (isset($msg['documentMessage'])) {
            return ['document', $msg['documentMessage']['fileName'] ?? null, $msg['documentMessage']['url'] ?? null];
        }

        if (isset($msg['stickerMessage'])) {
            return ['sticker', null, $msg['stickerMessage']['url'] ?? null];
        }

        return ['text', null, null];
    }
}
