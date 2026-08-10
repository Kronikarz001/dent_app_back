<?php

namespace App\Services;

use App\Exceptions\DefaultMessageGroupException;
use App\Exceptions\MessageGroupAccessDeniedException;
use App\Exceptions\MessageGroupMemberNotFoundException;
use App\Exceptions\MessageGroupMinimumMembersException;
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
    private const MINIMUM_MEMBERS = 2;

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
     * @return MessageGroup
     */
    public function getGroup(MessageGroup $group): MessageGroup
    {
        $this->assertMember($group);

        return $group->load(['creator', 'users']);
    }

    /**
     * @param MessageGroup $group
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(MessageGroup $group): LengthAwarePaginator
    {
        $this->assertMember($group);

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
        $this->assertMember($group);
        $this->assertNotDefault($group, 'Nie można edytować grupy domyślnej.');

        return $this->messageGroupRepository->update($group, ['name' => $data['name']]);
    }

    /**
     * @param MessageGroup $group
     * @return void
     */
    public function delete(MessageGroup $group): void
    {
        $this->assertMember($group);
        $this->assertNotDefault($group, 'Nie można usunąć grupy domyślnej.');

        $this->messageGroupRepository->delete($group);
    }

    /**
     * @param MessageGroup $group
     * @param User $user
     * @return void
     */
    public function addUser(MessageGroup $group, User $user): void
    {
        $this->assertMember($group);
        $this->assertNotDefault($group, 'Nie można ręcznie edytować grupy domyślnej.');

        $this->messageGroupRepository->addUser($group, $user->uuid);
    }

    /**
     * @param MessageGroup $group
     * @param User $user
     * @return void
     * @throws DefaultMessageGroupException
     * @throws MessageGroupAccessDeniedException
     * @throws MessageGroupMemberNotFoundException
     * @throws MessageGroupMinimumMembersException
     */
    public function removeUser(MessageGroup $group, User $user): void
    {
        $this->assertMember($group);
        $this->assertNotDefault($group, 'Nie można ręcznie edytować grupy domyślnej.');

        if (! $group->users()->where('users.uuid', $user->uuid)->exists()) {
            throw new MessageGroupMemberNotFoundException;
        }

        if ($this->messageGroupRepository->getMembersCount($group) <= self::MINIMUM_MEMBERS) {
            throw new MessageGroupMinimumMembersException;
        }

        $this->messageGroupRepository->removeUser($group, $user->uuid);
    }

    /**
     * @param MessageGroup $group
     * @return void
     */
    public function markGroupAsRead(MessageGroup $group): void
    {
        $this->assertMember($group);

        $this->messageGroupRepository->markAsRead($group, Auth::user()->uuid);
    }

    /**
     * @param MessageGroup $group
     * @return void
     */
    private function assertMember(MessageGroup $group): void
    {
        if (! $this->messageGroupRepository->hasMember($group, Auth::user()->uuid)) {
            throw new MessageGroupAccessDeniedException;
        }
    }

    /**
     * @param MessageGroup $group
     * @param string $message
     * @return void
     */
    private function assertNotDefault(MessageGroup $group, string $message): void
    {
        if ($group->is_default) {
            throw new DefaultMessageGroupException($message);
        }
    }
}
