<?php

namespace Tests\Unit\Repositories;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Repositories\MessageGroupRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of MessageGroupRepositoryTest
 */
class MessageGroupRepositoryTest extends TestCase
{
    /**
     * @var MessageGroupRepositoryInterface
     */
    private MessageGroupRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MessageGroupRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testAddUserAttachesUserToGroup(): void
    {
        $group = MessageGroup::factory()->create();
        $user = User::factory()->create();

        $this->repository->addUser($group, $user->uuid);

        $this->assertTrue($group->users->contains($user));
    }

    /**
     * @return void
     */
    public function testRemoveUserDetachesUserFromGroup(): void
    {
        $group = MessageGroup::factory()->create();
        $user = User::factory()->create();
        $group->users()->attach($user->uuid);

        $this->repository->removeUser($group, $user->uuid);

        $this->assertFalse($group->users()->get()->contains($user));
    }

    /**
     * @return void
     */
    public function testGetMembersCountReturnsNumberOfAttachedUsers(): void
    {
        $group = MessageGroup::factory()->create();
        $group->users()->attach(User::factory()->count(3)->create()->pluck('uuid'));

        $this->assertSame(3, $this->repository->getMembersCount($group));
    }

    /**
     * @return void
     */
    public function testMarkAsReadInsertsReadRowsForGroupsMessages(): void
    {
        $group = MessageGroup::factory()->create();
        $user = User::factory()->create();
        $message = Message::factory()->create(['message_group_uuid' => $group->uuid]);

        $this->repository->markAsRead($group, $user->uuid);

        $this->assertDatabaseHas('message_reads', [
            'message_uuid' => $message->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testFindAllForUserReturnsOnlyGroupsUserBelongsTo(): void
    {
        $user = User::factory()->create();
        $ownGroup = MessageGroup::factory()->create();
        $ownGroup->users()->attach($user->uuid);
        $unrelatedGroup = MessageGroup::factory()->create();

        $result = $this->repository->findAllForUser($user->uuid);

        $this->assertTrue($result->contains($ownGroup));
        $this->assertFalse($result->contains($unrelatedGroup));
    }

    /**
     * @return void
     */
    public function testHasMemberReturnsTrueOnlyForAttachedUser(): void
    {
        $group = MessageGroup::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        $group->users()->attach($member->uuid);

        $this->assertTrue($this->repository->hasMember($group, $member->uuid));
        $this->assertFalse($this->repository->hasMember($group, $stranger->uuid));
    }
}
