<?php

namespace App\Repositories;

use App\Models\Message;
use App\Models\User;
use App\Search\MessageSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of MessageRepository
 */
class MessageRepository extends SearchableRepository implements MessageRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Message::class;

    /**
     * @param MessageSearch $search
     */
    public function __construct(
        private MessageSearch $search,
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
     * @return Message|null
     */
    public function findByUuid(string $uuid): ?Message
    {
        return Message::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * @param Message|Model $model
     * @param array $data
     * @return Message
     */
    public function update(Message|Model $model, array $data): Message
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Message|Model $model
     * @return bool
     */
    public function delete(Message|Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param Message $message
     * @param string $groupUuid
     * @return Message
     */
    public function assignGroup(Message $message, string $groupUuid): Message
    {
        $message->message_group_uuid = $groupUuid;
        $message->save();

        return $message;
    }

    /**
     * @param Message $message
     * @param string $userUuid
     * @return void
     */
    public function markAsReadBy(Message $message, string $userUuid): void
    {
        $message->readBy()->syncWithoutDetaching([$userUuid]);
    }

    /**
     * @param User $user
     * @return int
     */
    public function countUnreadConversationsForUser(User $user): int
    {
        $groupUuids = $user->messageGroups()->pluck('message_groups.uuid');

        return Message::query()
            ->where('user_uuid', '!=', $user->uuid)
            ->where(function (Builder $query) use ($user, $groupUuids) {
                $query->where('recipient_user_uuid', $user->uuid)
                    ->orWhereIn('message_group_uuid', $groupUuids);
            })
            ->whereDoesntHave('readBy', fn (Builder $query) => $query->where('users.uuid', $user->uuid))
            ->count();
    }
}
