<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Exceptions\MessageAccessDeniedException;
use App\Exceptions\MessageGroupAccessDeniedException;
use App\Models\Message;
use App\Models\MessageGroup;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of MessageService
 */
readonly class MessageService implements MessageServiceInterface
{
    /**
     * @param MessageRepositoryInterface $messageRepository
     * @param MessageGroupRepositoryInterface $messageGroupRepository
     */
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
        private MessageGroupRepositoryInterface $messageGroupRepository,
    ) {}

    /**
     * @param array $data
     * @return Message
     */
    public function send(array $data): Message
    {
        $sender = Auth::user();

        if (! empty($data['message_group_uuid'])) {
            $this->assertSenderIsGroupMember($data['message_group_uuid'], $sender->uuid);

            $message = $this->messageRepository->create([
                'user_uuid' => $sender->uuid,
                'message_group_uuid' => $data['message_group_uuid'],
                'message' => $data['message'],
            ]);
        } elseif (! empty($data['recipient_uuid'])) {
            $message = $this->messageRepository->create([
                'user_uuid' => $sender->uuid,
                'recipient_user_uuid' => $data['recipient_uuid'],
                'message' => $data['message'],
            ]);
        } else {
            $defaultGroup = MessageGroup::where('is_default', true)->first();

            $message = $this->messageRepository->create([
                'user_uuid' => $sender->uuid,
                'message_group_uuid' => $defaultGroup->uuid,
                'message' => $data['message'],
            ]);
        }

        MessageSent::dispatch($message);

        return $message;
    }

    /**
     * @param string $messageGroupUuid
     * @param string $senderUuid
     * @return void
     */
    private function assertSenderIsGroupMember(string $messageGroupUuid, string $senderUuid): void
    {
        $group = $this->messageGroupRepository->findByUuid($messageGroupUuid);

        if ($group === null || ! $this->messageGroupRepository->hasMember($group, $senderUuid)) {
            throw new MessageGroupAccessDeniedException;
        }
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getInbox(): LengthAwarePaginator
    {
        $user = Auth::user();
        $userGroupUuids = $user->messageGroups()->pluck('message_groups.uuid')->toArray();

        return $this->messageRepository->findAllWithPagination([
            'for_user' => $user->uuid,
            'user_group_uuids' => $userGroupUuids,
        ]);
    }

    /**
     * @param Message $message
     * @return void
     */
    public function deleteMessage(Message $message): void
    {
        if ($message->user_uuid !== Auth::id()) {
            throw new MessageAccessDeniedException;
        }

        $this->messageRepository->delete($message);
    }

    /**
     * @param Message $message
     * @return void
     */
    public function markAsRead(Message $message): void
    {
        $userUuid = Auth::user()->uuid;

        if ($message->message_group_uuid !== null) {
            $isMember = $message->group()
                ->whereHas('users', fn ($query) => $query->where('users.uuid', $userUuid))
                ->exists();

            if (! $isMember) {
                return;
            }
        } elseif ($message->recipient_user_uuid !== $userUuid) {
            return;
        }

        $this->messageRepository->markAsReadBy($message, $userUuid);
    }

    /**
     * @return int
     */
    public function getUnreadConversationsCount(): int
    {
        return $this->messageRepository->countUnreadConversationsForUser(Auth::user());
    }
}
