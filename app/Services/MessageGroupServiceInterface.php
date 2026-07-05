<?php

namespace App\Services;

use App\Models\MessageGroup;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface MessageGroupServiceInterface
{
    public function getMyGroups(): LengthAwarePaginator;

    public function create(array $data): MessageGroup;

    public function update(MessageGroup $group, array $data): MessageGroup;

    public function delete(MessageGroup $group): void;

    public function addUser(MessageGroup $group, User $user): void;

    public function removeUser(MessageGroup $group, User $user): void;
}
