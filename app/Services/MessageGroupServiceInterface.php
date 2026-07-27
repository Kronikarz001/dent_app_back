<?php

namespace App\Services;

use App\Models\MessageGroup;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of MessageGroupServiceInterface
 */
interface MessageGroupServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getMyGroups(): LengthAwarePaginator;

    /**
     * @param MessageGroup $group
     * @return MessageGroup
     */
    public function getGroup(MessageGroup $group): MessageGroup;

    /**
     * @param MessageGroup $group
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(MessageGroup $group): LengthAwarePaginator;

    /**
     * @param array $data
     * @return MessageGroup
     */
    public function create(array $data): MessageGroup;

    /**
     * @param MessageGroup $group
     * @param array $data
     * @return MessageGroup
     */
    public function update(MessageGroup $group, array $data): MessageGroup;

    /**
     * @param MessageGroup $group
     * @return void
     */
    public function delete(MessageGroup $group): void;

    /**
     * @param MessageGroup $group
     * @param User $user
     * @return void
     */
    public function addUser(MessageGroup $group, User $user): void;

    /**
     * @param MessageGroup $group
     * @param User $user
     * @return void
     */
    public function removeUser(MessageGroup $group, User $user): void;

    /**
     * @param MessageGroup $group
     * @return void
     */
    public function markGroupAsRead(MessageGroup $group): void;
}
