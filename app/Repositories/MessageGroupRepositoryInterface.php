<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Model;

interface MessageGroupRepositoryInterface extends BasicRepositoryInterface
{
    public function findByUuid(string $uuid): ?MessageGroup;

    public function create(array $data): MessageGroup;

    public function update(MessageGroup|Model $model, array $data): MessageGroup;

    public function delete(Model|MessageGroup $model): bool;

    public function addUser(MessageGroup $group, string $userUuid): void;

    public function removeUser(MessageGroup $group, string $userUuid): void;

    public function getMembersCount(MessageGroup $group): int;
}
