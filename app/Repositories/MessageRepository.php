<?php

namespace App\Repositories;

use App\Models\Message;
use App\Models\User;
use App\Search\MessageSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of MessageRepository
 *
 * @extends SearchableRepository<Message>
 */
class MessageRepository extends SearchableRepository implements MessageRepositoryInterface
{
    /**
     * @var class-string<Message>
     */
    protected string $modelClass = Message::class;

    /**
     * @param MessageSearch $search
     */
    public function __construct(
        private readonly MessageSearch $search,
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
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
