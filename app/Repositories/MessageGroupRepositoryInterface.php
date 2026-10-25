<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use Illuminate\Support\Collection;

/**
 * Summary of MessageGroupRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<MessageGroup>
 */
interface MessageGroupRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function addUser(MessageGroup $group, string $userUuid): void;

    /**
     * @param MessageGroup $group
     * @param string[] $userUuids
     * @return void
     */
    public function addUsers(MessageGroup $group, array $userUuids): void;

    /**
     * @param MessageGroup $group
     * @return MessageGroup
     */
    public function loadDetails(MessageGroup $group): MessageGroup;

    /**
     * @return MessageGroup|null
     */
    public function findDefault(): ?MessageGroup;

    /**
     * @param string $userUuid
     * @return string[]
     */
    public function findUuidsForUser(string $userUuid): array;

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function removeUser(MessageGroup $group, string $userUuid): void;

    /**
     * @param MessageGroup $group
     * @return int
     */
    public function getMembersCount(MessageGroup $group): int;

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function markAsRead(MessageGroup $group, string $userUuid): void;

    /**
     * @param string $userUuid
     * @return Collection
     */
    public function findAllForUser(string $userUuid): Collection;

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return bool
     */
    public function hasMember(MessageGroup $group, string $userUuid): bool;
}
