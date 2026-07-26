<?php

namespace App\Services;

use App\Models\MessageGroup;
use App\Models\User;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of MessageGroupService
 */
readonly class MessageGroupService implements MessageGroupServiceInterface
{
    /**
     * @param MessageGroupRepositoryInterface $messageGroupRepository
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        private MessageGroupRepositoryInterface $messageGroupRepository,
        private MessageRepositoryInterface $messageRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getMyGroups(): LengthAwarePaginator
    {
        return $this->messageGroupRepository->findAllWithPagination([
            'for_user' => Auth::user()->uuid,
        ]);
    }

    /**
     * @param MessageGroup $group
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(MessageGroup $group): LengthAwarePaginator
    {
        return $this->messageRepository->findAllWithPagination(['message_group_uuid' => $group->uuid]);
    }

    /**
     * @param array $data
     * @return MessageGroup
     */
    public function create(array $data): MessageGroup
    {
        $creator = Auth::user();

        $group = $this->messageGroupRepository->create([
            'name' => $data['name'],
            'creator_uuid' => $creator->uuid,
        ]);

        $userUuids = array_unique(array_merge([$creator->uuid], $data['user_uuids']));
        $group->users()->attach($userUuids);

        return $group->load(['creator', 'users']);
    }

    /**
     * @param MessageGroup $group
     * @param array $data
     * @return MessageGroup
     */
    public function update(MessageGroup $group, array $data): MessageGroup
    {
        return $this->messageGroupRepository->update($group, ['name' => $data['name']]);
    }

    /**
     * @param MessageGroup $group
     * @return void
     */
    public function delete(MessageGroup $group): void
    {
        $this->messageGroupRepository->delete($group);
    }

    /**
     * @param MessageGroup $group
     * @param User $user
     * @return void
     */
    public function addUser(MessageGroup $group, User $user): void
    {
        $this->messageGroupRepository->addUser($group, $user->uuid);
    }

    /**
     * @param MessageGroup $group
     * @param User $user
     * @return void
     */
    public function removeUser(MessageGroup $group, User $user): void
    {
        $this->messageGroupRepository->removeUser($group, $user->uuid);
    }

    /**
     * @param MessageGroup $group
     * @return void
     */
    public function markGroupAsRead(MessageGroup $group): void
    {
        $this->messageGroupRepository->markAsRead($group, Auth::user()->uuid);
    }
}
