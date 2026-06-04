<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Resources;

use Am2tec\EvolutionSdk\EvolutionClient;

class InstanceResource
{
    public function __construct(private readonly EvolutionClient $client) {}

    public function create(string $name, array $options = []): array
    {
        return $this->client->post('instance/create', array_merge(['instanceName' => $name], $options));
    }

    public function fetch(?string $name = null): array
    {
        $query = $name ? ['instanceName' => $name] : [];

        return $this->client->get('instance/fetchInstances', $query);
    }

    public function connect(string $instance): array
    {
        return $this->client->get("instance/connect/{$instance}");
    }

    public function connectionState(string $instance): array
    {
        $data = $this->client->get("instance/connectionState/{$instance}");

        // v2 returns { instance: { instanceName, state } }; normalize to { state, instanceName }
        if (isset($data['instance']) && is_array($data['instance'])) {
            return $data['instance'];
        }

        return $data;
    }

    public function restart(string $instance): array
    {
        return $this->client->post("instance/restart/{$instance}");
    }

    public function logout(string $instance): array
    {
        return $this->client->delete("instance/logout/{$instance}");
    }

    public function delete(string $instance): array
    {
        return $this->client->delete("instance/delete/{$instance}");
    }

    public function setPresence(string $instance, string $presence): array
    {
        return $this->client->post("instance/setPresence/{$instance}", ['presence' => $presence]);
    }
}
