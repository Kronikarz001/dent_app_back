<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Summary of MessageGroupRepositoryInterface
 */
interface MessageGroupRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return MessageGroup|null
     */
    public function findByUuid(string $uuid): ?MessageGroup;

    /**
     * @param array $data
     * @return MessageGroup
     */
    public function create(array $data): MessageGroup;

    /**
     * @param MessageGroup|Model $model
     * @param array $data
     * @return MessageGroup
     */
    public function update(MessageGroup|Model $model, array $data): MessageGroup;

    /**
     * @param Model|MessageGroup $model
     * @return bool
     */
    public function delete(Model|MessageGroup $model): bool;

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function addUser(MessageGroup $group, string $userUuid): void;

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
