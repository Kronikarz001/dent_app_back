<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Model;

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
}
