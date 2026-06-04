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
