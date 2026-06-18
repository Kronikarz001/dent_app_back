<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of MessageRepositoryInterface
 */
interface MessageRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Message|null
     */
    public function findByUuid(string $uuid): ?Message;

    /**
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message;

    /**
     * @param Message|Model $model
     * @param array $data
     * @return Message
     */
    public function update(Message|Model $model, array $data): Message;

    /**
     * @param Model|Message $model
     * @return bool
     */
    public function delete(Model|Message $model): bool;

    /**
     * @param Message $message
     * @param string $groupUuid
     * @return Message
     */
    public function assignGroup(Message $message, string $groupUuid): Message;
}
