<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Resources;

use Am2tec\EvolutionSdk\EvolutionClient;

class GroupResource
{
    public function __construct(private readonly EvolutionClient $client) {}

    public function create(string $instance, string $subject, array $participants, string $description = ''): array
    {
        return $this->client->post("group/create/{$instance}", [
            'subject'      => $subject,
            'description'  => $description,
            'participants' => $participants,
        ]);
    }

    public function fetchAll(string $instance, bool $getParticipants = false): array
    {
        return $this->client->get("group/fetchAllGroups/{$instance}", [
            'getParticipants' => $getParticipants ? 'true' : 'false',
        ]);
    }

    public function findByJid(string $instance, string $groupJid): array
    {
        return $this->client->get("group/findGroupInfos/{$instance}", ['groupJid' => $groupJid]);
    }

    public function findMembers(string $instance, string $groupJid): array
    {
        return $this->client->get("group/participants/{$instance}", ['groupJid' => $groupJid]);
    }

    public function updateMembers(string $instance, string $groupJid, array $participants, string $action): array
    {
        return $this->client->put("group/updateParticipant/{$instance}", [
            'groupJid'     => $groupJid,
            'action'       => $action,
            'participants' => $participants,
        ]);
    }

    public function addParticipants(string $instance, string $groupJid, array $participants): array
    {
        return $this->updateMembers($instance, $groupJid, $participants, 'add');
    }

    public function removeParticipants(string $instance, string $groupJid, array $participants): array
    {
        return $this->updateMembers($instance, $groupJid, $participants, 'remove');
    }

    public function promoteParticipants(string $instance, string $groupJid, array $participants): array
    {
        return $this->updateMembers($instance, $groupJid, $participants, 'promote');
    }

    public function demoteParticipants(string $instance, string $groupJid, array $participants): array
    {
        return $this->updateMembers($instance, $groupJid, $participants, 'demote');
    }

    public function updateSubject(string $instance, string $groupJid, string $subject): array
    {
        return $this->client->put("group/updateGroupSubject/{$instance}", [
            'groupJid' => $groupJid,
            'subject'  => $subject,
        ]);
    }

    public function updateDescription(string $instance, string $groupJid, string $description): array
    {
        return $this->client->put("group/updateGroupDescription/{$instance}", [
            'groupJid'    => $groupJid,
            'description' => $description,
        ]);
    }

    public function fetchInviteCode(string $instance, string $groupJid): array
    {
        return $this->client->get("group/inviteCode/{$instance}", ['groupJid' => $groupJid]);
    }

    public function revokeInviteCode(string $instance, string $groupJid): array
    {
        return $this->client->put("group/revokeInviteCode/{$instance}", ['groupJid' => $groupJid]);
    }

    public function leave(string $instance, string $groupJid): array
    {
        return $this->client->delete("group/leaveGroup/{$instance}?groupJid={$groupJid}");
    }

    public function toggleEphemeral(string $instance, string $groupJid, int $expiration): array
    {
        return $this->client->post("group/toggleEphemeral/{$instance}", [
            'groupJid'   => $groupJid,
            'expiration' => $expiration,
        ]);
    }
}
