<?php

namespace App\Repositories;

use App\Models\Message;
use App\Models\User;

/**
 * Summary of MessageRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Message>
 */
interface MessageRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param Message $message
     * @param string $groupUuid
     * @return Message
     */
    public function assignGroup(Message $message, string $groupUuid): Message;

    /**
     * @param Message $message
     * @param string $userUuid
     * @return void
     */
    public function markAsReadBy(Message $message, string $userUuid): void;

    /**
     * @param User $user
     * @return int
     */
    public function countUnreadConversationsForUser(User $user): int;
}
