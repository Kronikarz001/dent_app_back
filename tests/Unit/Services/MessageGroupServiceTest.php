<?php

namespace Tests\Unit\Services;

use App\Models\MessageGroup;
use App\Models\User;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use App\Services\MessageGroupService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of MessageGroupServiceTest
 */
class MessageGroupServiceTest extends TestCase
{
    private MockInterface $messageGroupRepository;

    private MockInterface $messageRepository;

    private MessageGroupService $messageGroupService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageGroupRepository = Mockery::mock(MessageGroupRepositoryInterface::class);
        $this->messageRepository = Mockery::mock(MessageRepositoryInterface::class);
        $this->messageGroupService = new MessageGroupService($this->messageGroupRepository, $this->messageRepository);
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
    public function testGetGroupMessagesFiltersByGroupUuid(): void
    {
        $user = User::factory()->create();
        Auth::setUser($user);
        $group = MessageGroup::factory()->create();
        $group->users()->attach($user->uuid);
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->messageRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['message_group_uuid' => $group->uuid])
            ->andReturn($paginator);

        $result = $this->messageGroupService->getGroupMessages($group);

        $this->assertSame($paginator, $result);
    }
}
