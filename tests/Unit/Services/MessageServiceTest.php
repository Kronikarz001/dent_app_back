<?php

namespace Tests\Unit\Services;

use App\Exceptions\MessageAccessDeniedException;
use App\Exceptions\MessageGroupAccessDeniedException;
use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use App\Services\MessageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of MessageServiceTest
 */
class MessageServiceTest extends TestCase
{
    private MockInterface $messageRepository;

    private MockInterface $messageGroupRepository;

    private MessageService $messageService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->messageGroupRepository = Mockery::mock(MessageGroupRepositoryInterface::class);
        $this->messageService = new MessageService($this->messageRepository, $this->messageGroupRepository);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testSendDirectMessageToRecipient(): void
    {
        $sender = User::factory()->make(['uuid' => 'sender-uuid']);
        Auth::setUser($sender);
        $recipient = User::factory()->make(['uuid' => 'recipient-uuid']);
        $message = Message::factory()->make(['user_uuid' => 'sender-uuid']);

        $this->messageRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_uuid' => $sender->uuid,
                'recipient_user_uuid' => $recipient->uuid,
                'message' => 'Hej',
            ])
            ->andReturn($message);

        $result = $this->messageService->send(['message' => 'Hej', 'recipient_uuid' => $recipient->uuid]);

        $this->assertSame($message, $result);
    }

    /**
     * @return void
     */
    public function testSendReplyToExistingGroupDoesNotCreateNewGroup(): void
    {
        $sender = User::factory()->make(['uuid' => 'sender-uuid']);
        Auth::setUser($sender);
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid']);
        $message = Message::factory()->make(['user_uuid' => 'sender-uuid']);

        $this->messageGroupRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($group->uuid)
            ->andReturn($group);

        $this->messageGroupRepository
            ->shouldReceive('hasMember')
            ->once()
            ->with($group, $sender->uuid)
            ->andReturn(true);

        $this->messageRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_uuid' => $sender->uuid,
                'message_group_uuid' => $group->uuid,
                'message' => 'Odpowiedz',
            ])
            ->andReturn($message);

        $result = $this->messageService->send(['message' => 'Odpowiedz', 'message_group_uuid' => $group->uuid]);

        $this->assertSame($message, $result);
    }

    /**
     * @return void
     */
    public function testSendThrowsWhenSenderIsNotGroupMember(): void
    {
        $sender = User::factory()->make(['uuid' => 'sender-uuid']);
        Auth::setUser($sender);
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid']);

        $this->messageGroupRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($group->uuid)
            ->andReturn($group);

        $this->messageGroupRepository
            ->shouldReceive('hasMember')
            ->once()
            ->with($group, $sender->uuid)
            ->andReturn(false);

        $this->messageRepository->shouldNotReceive('create');

        $this->expectException(MessageGroupAccessDeniedException::class);

        $this->messageService->send(['message' => 'Odpowiedz', 'message_group_uuid' => $group->uuid]);
    }

    /**
     * @return void
     */
    public function testSendWithoutRecipientUsesDefaultGroup(): void
    {
        $sender = User::factory()->make(['uuid' => 'sender-uuid']);
        Auth::setUser($sender);
        $defaultGroup = MessageGroup::where('is_default', true)->firstOrFail();
        $message = Message::factory()->make([
            'user_uuid' => 'sender-uuid',
            'message_group_uuid' => $defaultGroup->uuid,
        ]);

        $this->messageRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_uuid' => $sender->uuid,
                'message_group_uuid' => $defaultGroup->uuid,
                'message' => 'Do wszystkich',
            ])
            ->andReturn($message);

        $result = $this->messageService->send(['message' => 'Do wszystkich']);

        $this->assertSame($message, $result);
    }

    /**
     * @return void
     */
    public function testGetInboxFiltersByLoggedUser(): void
    {
        $user = User::factory()->create();
        Auth::setUser($user);
        $userGroupUuids = $user->messageGroups()->pluck('message_groups.uuid')->toArray();
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->messageRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['for_user' => $user->uuid, 'user_group_uuids' => $userGroupUuids])
            ->andReturn($paginator);

        $result = $this->messageService->getInbox();

        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testDeleteMessageDelegatesToRepositoryWhenUserIsOwner(): void
    {
        $this->expectNotToPerformAssertions();
        $owner = User::factory()->make(['uuid' => 'owner-uuid']);
        Auth::setUser($owner);
        $message = Message::factory()->make(['uuid' => 'message-uuid', 'user_uuid' => 'owner-uuid']);

        $this->messageRepository
            ->shouldReceive('delete')
            ->once()
            ->with($message)
            ->andReturn(true);

        $this->messageService->deleteMessage($message);
    }

    /**
     * @return void
     */
    public function testDeleteMessageThrowsWhenUserIsNotOwner(): void
    {
        $user = User::factory()->make(['uuid' => 'someone-else-uuid']);
        Auth::setUser($user);
        $message = Message::factory()->make(['uuid' => 'message-uuid', 'user_uuid' => 'owner-uuid']);

        $this->messageRepository->shouldNotReceive('delete');

        $this->expectException(MessageAccessDeniedException::class);

        $this->messageService->deleteMessage($message);
    }

    /**
     * @return void
     */
    public function testMarkAsReadUpdatesMessageWhenUserIsRecipient(): void
    {
        $this->expectNotToPerformAssertions();
        $user = User::factory()->make(['uuid' => 'recipient-uuid']);
        Auth::setUser($user);
        $message = Message::factory()->make(['user_uuid' => 'sender-uuid', 'recipient_user_uuid' => 'recipient-uuid']);

        $this->messageRepository
            ->shouldReceive('markAsReadBy')
            ->once()
            ->with($message, 'recipient-uuid');

        $this->messageService->markAsRead($message);
    }

    /**
     * @return void
     */
    public function testMarkAsReadDoesNothingWhenUserIsNotRecipient(): void
    {
        $this->expectNotToPerformAssertions();
        $user = User::factory()->make(['uuid' => 'someone-else-uuid']);
        Auth::setUser($user);
        $message = Message::factory()->make(['user_uuid' => 'sender-uuid', 'recipient_user_uuid' => 'recipient-uuid']);

        $this->messageRepository->shouldNotReceive('markAsReadBy');

        $this->messageService->markAsRead($message);
    }

    /**
     * @return void
     */
    public function testGetUnreadConversationsCountDelegatesToRepositoryForAuthenticatedUser(): void
    {
        $user = User::factory()->make(['uuid' => 'me-uuid']);
        Auth::setUser($user);

        $this->messageRepository
            ->shouldReceive('countUnreadConversationsForUser')
            ->once()
            ->with($user)
            ->andReturn(3);

        $result = $this->messageService->getUnreadConversationsCount();

        $this->assertSame(3, $result);
    }
}
