<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageGroup;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of MessageServiceInterface
 */
interface MessageServiceInterface
{
    /**
     * @param array $data
     * @return Message
     */
    public function send(array $data): Message;

    /**
     * @return LengthAwarePaginator
     */
    public function getInbox(): LengthAwarePaginator;

    /**
     * @param MessageGroup $messageGroup
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(MessageGroup $messageGroup): LengthAwarePaginator;
}
