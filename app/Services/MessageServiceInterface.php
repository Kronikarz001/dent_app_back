<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of MessageServiceInterface
 */
interface MessageServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getInbox(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Message
     */
    public function send(array $data): Message;

    /**
     * @param Message $message
     * @return void
     */
    public function deleteMessage(Message $message): void;

    /**
     * @param Message $message
     * @return void
     */
    public function markAsRead(Message $message): void;

    /**
     * @return int
     */
    public function getUnreadConversationsCount(): int;
}
