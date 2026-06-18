<?php

namespace App\Services;

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

        return $this->broadcast($sender->uuid, $data['message']);
    }

    /**
     * @param string $senderUuid
     * @param string $message
     * @return Message
     */
    private function broadcast(string $senderUuid, string $message): Message
    {
        $rootMessage = $this->messageRepository->create([
            'user_uuid' => $senderUuid,
            'message' => $message,
        ]);

        $group = $this->messageGroupRepository->create([
            'message_uuid' => $rootMessage->uuid,
        ]);

        return $this->messageRepository->assignGroup($rootMessage, $group->uuid);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getInbox(): LengthAwarePaginator
    {
        return $this->messageRepository->findAllWithPagination(['for_user' => Auth::id()]);
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
