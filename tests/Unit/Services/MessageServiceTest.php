<?php

namespace Tests\Unit\Services;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
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

    private MessageService $messageService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->messageService = new MessageService($this->messageRepository);
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
    public function testGetGroupMessagesFiltersByGroupUuid(): void
    {
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid']);
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
