<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Resources;

use Am2tec\EvolutionSdk\EvolutionClient;

class SettingsResource
{
    public function __construct(private readonly EvolutionClient $client) {}

    /**
     * @return array<string, mixed>
     */
    public function find(string $instance): array
    {
        return $this->client->get("settings/find/{$instance}");
    }

    /**
     * Persist settings for the given instance.
     * Evolution v2 POST /settings/set expects camelCase; GET /settings/find returns snake_case.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function set(string $instance, array $settings): array
    {
        return $this->client->post("settings/set/{$instance}", $settings);
    }

    /**
     * Merge current settings with overrides — reads first to preserve unchanged fields.
     *
     * @param  array<string, mixed>  $overrides  camelCase keys
     * @return array<string, mixed>
     */
    public function merge(string $instance, array $overrides): array
    {
        $current = $this->find($instance);

        $merged = array_merge([
            'rejectCall' => $current['reject_call'] ?? false,
            'msgCall' => $current['msg_call'] ?? '',
            'groupsIgnore' => $current['groups_ignore'] ?? false,
            'alwaysOnline' => $current['always_online'] ?? false,
            'readMessages' => $current['read_messages'] ?? false,
            'readStatus' => $current['read_status'] ?? false,
            'syncFullHistory' => $current['sync_full_history'] ?? false,
        ], $overrides);

        return $this->set($instance, $merged);
    }
}
