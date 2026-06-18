<?php

namespace Tests\Unit\Services;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use App\Services\MessageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

        $this->messageGroupRepository->shouldNotReceive('create');

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
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid', 'message_uuid' => 'root-uuid']);
        $message = Message::factory()->make(['user_uuid' => 'sender-uuid']);

        $this->messageRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_uuid' => $sender->uuid,
                'message_group_uuid' => $group->uuid,
                'message' => 'Odpowiedz',
            ])
            ->andReturn($message);

        $this->messageGroupRepository->shouldNotReceive('create');

        $result = $this->messageService->send(['message' => 'Odpowiedz', 'message_group_uuid' => $group->uuid]);

        $this->assertSame($message, $result);
    }

    /**
     * @return void
     */
    public function testSendWithoutRecipientCreatesBroadcastGroup(): void
    {
        $sender = User::factory()->make(['uuid' => 'sender-uuid']);
        Auth::setUser($sender);
        $rootMessage = Message::factory()->make(['uuid' => 'root-message-uuid', 'user_uuid' => 'sender-uuid']);
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid', 'message_uuid' => $rootMessage->uuid]);
        $finalMessage = Message::factory()->make(['user_uuid' => 'sender-uuid', 'message_group_uuid' => $group->uuid]);

        $this->messageRepository
            ->shouldReceive('create')
            ->once()
            ->with(['user_uuid' => $sender->uuid, 'message' => 'Do wszystkich'])
            ->andReturn($rootMessage);

        $this->messageGroupRepository
            ->shouldReceive('create')
            ->once()
            ->with(['message_uuid' => $rootMessage->uuid])
            ->andReturn($group);

        $this->messageRepository
            ->shouldReceive('assignGroup')
            ->once()
            ->with($rootMessage, $group->uuid)
            ->andReturn($finalMessage);

        $result = $this->messageService->send(['message' => 'Do wszystkich']);

        $this->assertSame($finalMessage, $result);
    }

    /**
     * @return void
     */
    public function testGetInboxFiltersByLoggedUser(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid']);
        Auth::setUser($user);
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->messageRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['for_user' => $user->uuid])
            ->andReturn($paginator);

        $result = $this->messageService->getInbox();

        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testGetGroupMessagesFiltersByGroupUuid(): void
    {
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid', 'message_uuid' => 'root-uuid']);
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->messageRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['message_group_uuid' => $group->uuid])
            ->andReturn($paginator);

        $result = $this->messageService->getGroupMessages($group);

        $this->assertSame($paginator, $result);
    }
}
