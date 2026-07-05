<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageGroup;
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
     */
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
    ) {}

    /**
     * @param array $data
     * @return Message
     */
    public function send(array $data): Message
    {
        $sender = Auth::user();

        if (! empty($data['message_group_uuid'])) {
            return $this->messageRepository->create([
                'user_uuid' => $sender->uuid,
                'message_group_uuid' => $data['message_group_uuid'],
                'message' => $data['message'],
            ]);
        }

        if (! empty($data['recipient_uuid'])) {
            return $this->messageRepository->create([
                'user_uuid' => $sender->uuid,
                'recipient_user_uuid' => $data['recipient_uuid'],
                'message' => $data['message'],
            ]);
        }

        $defaultGroup = MessageGroup::where('is_default', true)->first();

        return $this->messageRepository->create([
            'user_uuid' => $sender->uuid,
            'message_group_uuid' => $defaultGroup->uuid,
            'message' => $data['message'],
        ]);
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
     * @param MessageGroup $messageGroup
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(MessageGroup $messageGroup): LengthAwarePaginator
    {
        return $this->messageRepository->findAllWithPagination(['message_group_uuid' => $messageGroup->uuid]);
    }
}
