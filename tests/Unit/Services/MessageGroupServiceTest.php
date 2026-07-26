<?php

namespace Tests\Unit\Services;

use App\Models\MessageGroup;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;
use App\Services\MessageGroupService;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $group = MessageGroup::factory()->make(['uuid' => 'group-uuid']);
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
