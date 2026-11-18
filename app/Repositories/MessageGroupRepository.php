<?php

namespace App\Repositories;

use App\Models\MessageGroup;
use App\Search\MessageGroupSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Summary of MessageGroupRepository
 *
 * @extends SearchableRepository<MessageGroup>
 */
class MessageGroupRepository extends SearchableRepository implements MessageGroupRepositoryInterface
{
    /**
     * @var class-string<MessageGroup>
     */
    protected string $modelClass = MessageGroup::class;

    /**
     * @param MessageGroupSearch $search
     */
    public function __construct(
        private readonly MessageGroupSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
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
     * @param string[] $userUuids
     * @return void
     */
    public function addUsers(MessageGroup $group, array $userUuids): void
    {
        $group->users()->attach($userUuids);
    }

    /**
     * @param MessageGroup $group
     * @return MessageGroup
     */
    public function loadDetails(MessageGroup $group): MessageGroup
    {
        return $group->load(['creator', 'users']);
    }

    /**
     * @return MessageGroup|null
     */
    public function findDefault(): ?MessageGroup
    {
        return $this->query()->where('is_default', true)->first();
    }

    /**
     * @param string $userUuid
     * @return string[]
     */
    public function findUuidsForUser(string $userUuid): array
    {
        return $this->query()
            ->whereHas('users', fn (Builder $query) => $query->where('users.uuid', $userUuid))
            ->pluck('uuid')
            ->all();
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
