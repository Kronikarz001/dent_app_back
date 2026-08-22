<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use App\Search\MessageGroupSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Summary of MessageGroupRepository
 */
class MessageGroupRepository extends SearchableRepository implements MessageGroupRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = MessageGroup::class;

    /**
     * @param MessageGroupSearch $search
     */
    public function __construct(
        private MessageGroupSearch $search
    ) {}

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $uuid
     * @return MessageGroup|null
     */
    public function findByUuid(string $uuid): ?MessageGroup
    {
        return MessageGroup::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return MessageGroup
     */
    public function create(array $data): MessageGroup
    {
        return MessageGroup::create($data);
    }

    /**
     * @param MessageGroup|Model $model
     * @param array $data
     * @return MessageGroup
     */
    public function update(MessageGroup|Model $model, array $data): MessageGroup
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Model|MessageGroup $model
     * @return bool
     */
    public function delete(Model|MessageGroup $model): bool
    {
        return $model->delete();
    }

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function addUser(MessageGroup $group, string $userUuid): void
    {
        $group->users()->attach($userUuid);
    }

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function removeUser(MessageGroup $group, string $userUuid): void
    {
        $group->users()->detach($userUuid);
    }

    /**
     * @param MessageGroup $group
     * @return int
     */
    public function getMembersCount(MessageGroup $group): int
    {
        return $group->users()->count();
    }

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return void
     */
    public function markAsRead(MessageGroup $group, string $userUuid): void
    {
        $now = now();
        $rows = $group->messages()->pluck('uuid')->map(fn (string $messageUuid) => [
            'message_uuid' => $messageUuid,
            'user_uuid' => $userUuid,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($rows->isNotEmpty()) {
            DB::table('message_reads')->upsert($rows->all(), ['message_uuid', 'user_uuid'], ['updated_at']);
        }
    }

    /**
     * @param string $userUuid
     * @return Collection
     */
    public function findAllForUser(string $userUuid): Collection
    {
        return MessageGroup::whereHas('users', function (Builder $query) use ($userUuid) {
            $query->where('users.uuid', $userUuid);
        })->with('users')->get();
    }

    /**
     * @param MessageGroup $group
     * @param string $userUuid
     * @return bool
     */
    public function hasMember(MessageGroup $group, string $userUuid): bool
    {
        return $group->users()->where('users.uuid', $userUuid)->exists();
    }
}
