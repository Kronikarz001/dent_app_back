<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
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
     * @param User $user
     * @return LengthAwarePaginator
     */
    public function getAllMessageForUser(User $user): LengthAwarePaginator;

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
}
