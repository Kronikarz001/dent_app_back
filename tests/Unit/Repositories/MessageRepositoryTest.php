<?php

namespace Tests\Unit\Repositories;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Repositories\MessageRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of MessageRepositoryTest
 */
class MessageRepositoryTest extends TestCase
{
    /**
     * @var MessageRepositoryInterface
     */
    private MessageRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MessageRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testAssignGroupSetsMessageGroupUuid(): void
    {
        $message = Message::factory()->create();
        $group = MessageGroup::factory()->create();

        $updated = $this->repository->assignGroup($message, $group->uuid);

        $this->assertSame($group->uuid, $updated->message_group_uuid);
    }

    /**
     * @return void
     */
    public function testMarkAsReadByAttachesUserWithoutDuplicating(): void
    {
        $message = Message::factory()->create();
        $user = User::factory()->create();

        $this->repository->markAsReadBy($message, $user->uuid);
        $this->repository->markAsReadBy($message, $user->uuid);

        $this->assertCount(1, $message->readBy);
    }

    /**
     * @return void
     */
    public function testCountUnreadConversationsForUserCountsDirectUnreadMessages(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        Message::factory()->create(['user_uuid' => $sender->uuid, 'recipient_user_uuid' => $user->uuid]);

        $this->assertSame(1, $this->repository->countUnreadConversationsForUser($user));
    }

    /**
     * @return void
     */
    public function testCountUnreadConversationsForUserExcludesAlreadyReadMessages(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $message = Message::factory()->create(['user_uuid' => $sender->uuid, 'recipient_user_uuid' => $user->uuid]);
        $message->readBy()->attach($user->uuid);

        $this->assertSame(0, $this->repository->countUnreadConversationsForUser($user));
    }

    /**
     * @return void
     */
    public function testCountUnreadConversationsForUserCountsUnreadGroupMessages(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach($user->uuid);
        Message::factory()->create(['user_uuid' => $sender->uuid, 'message_group_uuid' => $group->uuid]);

        $this->assertSame(1, $this->repository->countUnreadConversationsForUser($user));
    }
}
