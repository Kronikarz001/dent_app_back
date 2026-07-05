<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use App\Search\MessageGroupSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class MessageGroupRepository extends SearchableRepository implements MessageGroupRepositoryInterface
{
    protected string $modelClass = MessageGroup::class;

    public function __construct(
        private MessageGroupSearch $search
    ) {}

    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    public function findByUuid(string $uuid): ?MessageGroup
    {
        return MessageGroup::where('uuid', $uuid)->first();
    }

    public function create(array $data): MessageGroup
    {
        return MessageGroup::create($data);
    }

    public function update(MessageGroup|Model $model, array $data): MessageGroup
    {
        $model->update($data);

        return $model->fresh();
    }

    public function delete(Model|MessageGroup $model): bool
    {
        return $model->delete();
    }

    public function addUser(MessageGroup $group, string $userUuid): void
    {
        $group->users()->attach($userUuid);
    }

    public function removeUser(MessageGroup $group, string $userUuid): void
    {
        $group->users()->detach($userUuid);
    }

    public function getMembersCount(MessageGroup $group): int
    {
        return $group->users()->count();
    }
}
